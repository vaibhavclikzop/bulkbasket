<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\DB;

class EInvoiceService
{
    public function generate($invoiceId)
    {

        try {
            $response = Http::post("https://sandb-api.mastersindia.co/api/v1/token-auth/", [
                "username" => "shubhamgoyal@bulkbasketindia.com",
                // "grant_type" => "password",
                "password" => "Basketindia@123",
                // "client_id" => "IsFowZLGfhMbzTRfIk",
                // "client_secret" => "nGoe8NELkE1zNM0MfmdZrIvD"
            ]);
            if (!$response->successful()) {

                return response()->json([
                    'status' => true,
                    'message' => 'API returned error',
                    'error' => $response->json() ?? $response->body()
                ], 500);
            }

            $data = $response->json();
            if (!isset($data['token'])) {

                return response()->json([
                    'status' => true,
                    'message' => 'Invalid API response',
                    'error' => $data
                ], 500);
            }
            $token = $data['token'];
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'error' => $th->getTrace()
            ], 500);
        }


        return  $this->geneRateEInvoice($invoiceId, $token);
    }


    private function geneRateEInvoice($invoiceId, $token)
    {
        try {

            $orderDetSub = DB::table('orders_item')
                ->select(
                    'product_id',
                    'order_id',
                    DB::raw('MAX(gst) as gst')
                )
                ->groupBy('product_id', 'order_id');

            $invoiceMst = DB::table('stock_outward_mst as a')
                ->select(
                    "a.invoice_id",
                    "a.invoice_date",
                    "b.name as company",
                    "b.gst",
                    "b.address",
                    "b.city",
                    "b.pincode"
                )
                ->join("customers as b", "a.customer_id", "b.id")
                ->where("a.id", $invoiceId)
                ->first();

            if (!$invoiceMst) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice not found'
                ]);
            }

            $invoiceDet = DB::table("stock_outward_det as a")
                ->select(
                    "a.*",
                    "b.name as product",
                    "b.hsn_code",
                    "f.gst as order_gst"
                )
                ->join("products as b", "a.product_id", "b.id")
                ->join("stock_outward_mst as c", "a.mst_id", "c.id")
                ->join("orders_item as d", "c.order_id", "d.id")
                ->joinSub($orderDetSub, 'f', function ($join) {
                    $join->on("a.product_id", "=", "f.product_id")
                        ->on("d.id", "=", "f.order_id");
                })
                ->where("a.mst_id", $invoiceId)
                ->get();

            $itemList = [];
            $totalAssessableValue = 0;
            $totalIgstValue = 0;
            $totalCgstValue = 0;
            $totalSgstValue = 0;
            $totalInvoiceValue = 0;

            $serial = 1;
            $stateCode = substr($invoiceMst->gst, 0, 2);

            foreach ($invoiceDet as $row) {

                $qty = (float)$row->qty;
                $price = (float)$row->price;
                $gstRate = (float)$row->order_gst;

                $assessableValue = round($price, 2);

                $igst = 0;
                $cgst = 0;
                $sgst = 0;

                if ($stateCode == "03") {
                    $cgst = round(($assessableValue * $gstRate) / 200, 2);
                    $sgst = round(($assessableValue * $gstRate) / 200, 2);
                } else {
                    $igst = round(($assessableValue * $gstRate) / 100, 2);
                }

                $total = $assessableValue + $igst + $cgst + $sgst;

                // totals
                $totalAssessableValue += $assessableValue * $qty;
                $totalIgstValue += $igst * $qty;
                $totalCgstValue += $cgst * $qty;
                $totalSgstValue += $sgst * $qty;
                $totalInvoiceValue += $total * $qty;

                $itemList[] = [
                    "item_serial_number" => (string)$serial++,
                    "product_description" => $row->product,
                    "is_service" => "N",
                    "hsn_code" => substr($row->hsn_code, 0, 8),
                    "quantity" => $qty,
                    "unit" => "PCS",
                    "unit_price" => $assessableValue,
                    "total_amount" => round($assessableValue * $qty, 2),
                    "discount" => 0,
                    "other_charge" => 0,
                    "assessable_value" => round($assessableValue * $qty, 2),
                    "gst_rate" => $gstRate,
                    "igst_amount" => round($igst * $qty, 2),
                    "cgst_amount" => round($cgst * $qty, 2),
                    "sgst_amount" => round($sgst * $qty, 2),
                    "cess_rate" => 0,
                    "cess_amount" => 0,
                    "total_item_value" => round($total * $qty, 2)
                ];
            }

            $payload = [
                $gstin = "05AAAPG7885R002",

                "user_gstin" => $gstin,
                "data_source" => "erp",

                "transaction_details" => [
                    "supply_type" => "B2B",
                    "charge_type" => "N"
                ],

                "document_details" => [
                    "document_type" => "INV",
                    "document_number" => substr($invoiceMst->invoice_id, 0, 16),
                    "document_date" => \Carbon\Carbon::parse($invoiceMst->invoice_date)->format('d/m/Y'),
                ],

                "seller_details" => [
                    "gstin" => $gstin,
                    "legal_name" => "Bulk Basket India",
                    "address1" => "Uttarakhand",
                    "location" => "Uttarakhand",
                    "pincode" => 248001,
                    "state_code" => substr($gstin, 0, 2)
                ],

                // "buyer_details" => [
                //     "gstin" => $invoiceMst->gst,
                //     "legal_name" => $invoiceMst->company,
                //     "address1" => $invoiceMst->address,
                //     "location" => $invoiceMst->city,
                //     "pincode" => $invoiceMst->pincode,
                //     "state_code" => substr($invoiceMst->gst, 0, 2),
                //     "place_of_supply" => substr($invoiceMst->gst, 0, 2)
                // ],
                "buyer_details" => [
                    "gstin" => "09AAAPG7885R002",
                    "legal_name" => "Test Customer Pvt Ltd",
                    "address1" => "Sector 62, Noida",
                    "location" => "Noida",
                    "pincode" => 201301,
                    "state_code" => "09",
                    "place_of_supply" => "09"
                ],

                "item_list" => $itemList,

                "value_details" => [
                    "total_assessable_value" => round($totalAssessableValue, 2),
                    "total_igst_value" => round($totalIgstValue, 2),
                    "total_cgst_value" => round($totalCgstValue, 2),
                    "total_sgst_value" => round($totalSgstValue, 2),
                    "total_invoice_value" => round($totalInvoiceValue, 2),
                    "round_off_amount" => 0
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'JWT ' . $token,
                'Accept' => 'application/json'
            ])->post("https://sandb-api.mastersindia.co/api/v1/einvoice/", $payload);

            if (!$response->successful()) {
                return response()->json([
                    'status' => false,
                    'message' => 'API Error',
                    'error' => $response->body()
                ]);
            }

            $data = $response->json();

            if (($data['results']['status'] ?? '') === "Success") {

                DB::table("stock_outward_mst")
                    ->where("id", $invoiceId)
                    ->update([
                        "is_e_invoice" => 1,
                        "AckNo" => $data["results"]["message"]["AckNo"],
                        "AckDt" => $data["results"]["message"]["AckDt"],
                        "Irn" => $data["results"]["message"]["Irn"],
                        "SignedInvoice" => $data["results"]["message"]["SignedInvoice"],
                        "SignedQRCode" => $data["results"]["message"]["SignedQRCode"],
                        "QRCodeUrl" => $data["results"]["message"]["QRCodeUrl"],
                        "EinvoicePdf" => $data["results"]["message"]["EinvoicePdf"],
                        "e_invoice_response" => $data,
                    ]);

                return response()->json([
                    'status' => true,
                    'message' => 'E-Invoice generated successfully'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => $data['results']['message'] ?? 'Failed',
                'error' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

   
}
