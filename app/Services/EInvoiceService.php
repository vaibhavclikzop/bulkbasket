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

    // private function geneRateEInvoice($invoiceId, $token)
    // {
    //     try {
    //         $orderDetSub = DB::table('orders_item')
    //             ->select(
    //                 'product_id',
    //                 'order_id',
    //                 DB::raw('MAX(gst) as gst'),
    //             )
    //             ->groupBy('product_id', 'order_id');
    //         $invoiceMst = DB::table('stock_outward_mst as a')
    //             ->select(
    //                 "a.invoice_id",
    //                 "a.invoice_date",
    //                 "b.name as company",
    //                 "b.gst",
    //                 "b.address",
    //                 "b.state",
    //                 "b.city",
    //                 "b.pincode"
    //             )
    //             ->join("customers as b", "a.customer_id", "b.id")
    //             ->where("a.id", $invoiceId)
    //             ->first();
    //         if (!$invoiceMst) {
    //             return response()->json(['status' => false, 'message' => 'Invoice not found'], 404);
    //         }
    //         $invoiceDet = DB::table("stock_outward_det as a")
    //             ->select(
    //                 "a.*",
    //                 "b.name as product",
    //                 "b.article_no as part_code",
    //                 "e.name as brand",
    //                 "b.hsn_code",
    //                 "f.gst as order_gst",
    //             )
    //             ->join("products as b", "a.product_id", "b.id")
    //             ->join("stock_outward_mst as c", "a.mst_id", "c.id")
    //             ->join("orders_item as d", "c.order_id", "d.id")
    //             ->join("product_brand as e", "b.brand_id", "e.id")
    //             ->joinSub($orderDetSub, 'f', function ($join) {
    //                 $join->on("a.product_id", "=", "f.product_id")
    //                     ->on("d.id", "=", "f.order_id");
    //             })
    //             ->where("a.mst_id", $invoiceId)
    //             ->get();
    //         $itemList = [];
    //         $totalAssessableValue = 0;
    //         $totalIgstValue = 0;
    //         $totalCgstValue = 0;
    //         $totalSgstValue = 0;
    //         $totalInvoiceValue = 0;
    //         $serial = 1;
    //         $stateCode = substr($invoiceMst->gst, 0, 2);
    //         foreach ($invoiceDet as $row) {
    //             $qty = (float) $row->qty;
    //             $price = (float) $row->price;
    //             $discount = 0.00;
    //             $specialDiscount = 0.00;
    //             $gstRate = (float) $row->order_gst;
    //             $baseAmount = $price;
    //             $discountAmount = round(($baseAmount * $discount) / 100, 2);
    //             $afterDiscount = $baseAmount - $discountAmount;
    //             $specialDiscountAmount = round(($afterDiscount * $specialDiscount) / 100, 2);
    //             $assessableValue = round($afterDiscount - $specialDiscountAmount, 2);
    //             $igstAmount = 0;
    //             $cgstAmount = 0;
    //             $sgstAmount = 0;
    //             if ($stateCode == "03") {
    //                 $cgstAmount = round(($assessableValue * $gstRate) / 200, 2);
    //                 $sgstAmount = round(($assessableValue * $gstRate) / 200, 2);
    //             } else {
    //                 $igstAmount = round(($assessableValue * $gstRate) / 100, 2);
    //             }
    //             $totalItemValue = round($assessableValue + $igstAmount + $cgstAmount + $sgstAmount, 2);
    //             $totalAssessableValue += $assessableValue * $qty;
    //             $totalIgstValue += $igstAmount * $qty;
    //             $totalCgstValue += $cgstAmount * $qty;
    //             $totalSgstValue += $sgstAmount * $qty;
    //             $totalInvoiceValue += $totalItemValue * $qty;
    //             $itemList[] = [
    //                 "item_serial_number" => (string) $serial++,
    //                 "product_description" => $row->product,
    //                 "is_service" => "N",
    //                 "hsn_code" => $row->hsn_code,
    //                 "quantity" => $qty,
    //                 "unit" => "PCS",
    //                 "unit_price" => round($assessableValue, 2),
    //                 "total_amount" => round($assessableValue * $qty, 2),
    //                 "discount" => 0,
    //                 "other_charge" => 0,
    //                 "assessable_value" => round($assessableValue * $qty, 2),
    //                 "gst_rate" => $gstRate,
    //                 "igst_amount" => round($igstAmount * $qty, 2),
    //                 "cgst_amount" => round($cgstAmount * $qty, 2),
    //                 "sgst_amount" => round($sgstAmount * $qty, 2),
    //                 "cess_rate" => 0,
    //                 "cess_amount" => 0,
    //                 "total_item_value" => round($totalItemValue * $qty, 2)
    //             ];
    //         }
    //         $payload = [
    //             "user_gstin" => "04AANCB9120C1Z1", 
    //             "data_source" => "erp",
    //             "transaction_details" => [
    //                 "supply_type" => "B2B",
    //                 "charge_type" => "N"
    //             ],
    //             "document_details" => [
    //                 "document_type" => "INV",
    //                 "document_number" => $invoiceMst->invoice_id,
    //                 "document_date" => date("d/m/Y", strtotime($invoiceMst->invoice_date))
    //             ],
    //             "seller_details" => [
    //                 "gstin" => "05AAAPG7885R002",  
    //                 "legal_name" => "Bulk Basket India",
    //                 "address1" => "SCF 179, GRAIN MARKET, SECTOR 26, Chandigarh",
    //                 "location" => "Chandigarh",
    //                 "pincode" => 160019,  
    //                 "state_code" => "03"
    //             ],
    //             "buyer_details" => [
    //                 "gstin" => "09AAAPG7885R002",  
    //                 "legal_name" => $invoiceMst->company,
    //                 "address1" => $invoiceMst->address,
    //                 "location" => $invoiceMst->city,
    //                 "pincode" => $invoiceMst->pincode,
    //                 "state_code" => substr($invoiceMst->gst, 0, 2),
    //                 "place_of_supply" => substr($invoiceMst->gst, 0, 2)
    //             ],
    //             "item_list" => $itemList,
    //             "value_details" => [
    //                 "total_assessable_value" => round($totalAssessableValue, 2),
    //                 "total_igst_value" => round($totalIgstValue, 2),
    //                 "total_cgst_value" => round($totalCgstValue, 2),
    //                 "total_sgst_value" => round($totalSgstValue, 2),
    //                 "total_invoice_value" => round($totalInvoiceValue, 2),
    //                 "round_off_amount" => 0
    //             ]
    //         ];
    //         $response = Http::withHeaders([
    //             'Authorization' => 'Bearer ' . $token,
    //             'Content-Type' => 'application/json',
    //             'Accept' => 'application/json'
    //         ])->post("https://sandb-api.mastersindia.co/api/v1/einvoice/generate", $payload);
    //         if (!$response->successful()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'API Error',
    //                 'error' => $response->body()
    //             ], 500);
    //         }
    //         $data = $response->json();
    //         if ($data['results']['status'] === "Success") {
    //             DB::table("stock_outward_mst")
    //                 ->where("id", $invoiceId)
    //                 ->update([
    //                     "is_e_invoice" => 1,
    //                     "AckNo" => $data["results"]["message"]["AckNo"],
    //                     "AckDt" => $data["results"]["message"]["AckDt"],
    //                     "Irn" => $data["results"]["message"]["Irn"],
    //                     "SignedInvoice" => $data["results"]["message"]["SignedInvoice"],
    //                     "SignedQRCode" => $data["results"]["message"]["SignedQRCode"],
    //                     "QRCodeUrl" => $data["results"]["message"]["QRCodeUrl"],
    //                     "EinvoicePdf" => $data["results"]["message"]["EinvoicePdf"],
    //                     "e_invoice_response" => json_encode($data)
    //                 ]);
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'E-Invoice generated successfully'
    //             ]);
    //         }
    //         return response()->json([
    //             'status' => false,
    //             'message' => $data['results']['errorMessage'] ?? 'Failed',
    //             'error' => $data
    //         ], 500);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }

    private function geneRateEInvoice($invoiceId, $token)
    {
        try {

            // ================= SUB QUERY =================
            $orderDetSub = DB::table('orders_item')
                ->select(
                    'product_id',
                    'order_id',
                    DB::raw('MAX(gst) as gst')
                )
                ->groupBy('product_id', 'order_id');

            // ================= INVOICE MASTER =================
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

            // ================= INVOICE DETAILS =================
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

            // ================= CALCULATION =================
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

            // ================= PAYLOAD =================
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

            // ================= API CALL =================
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
