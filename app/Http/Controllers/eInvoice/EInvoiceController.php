<?php

namespace App\Http\Controllers\eInvoice;

use App\Http\Controllers\Controller;
use App\Services\EInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class EInvoiceController extends Controller
{
    public function generateEInvoice(Request $request, EInvoiceService $service)
    {
        return $service->generate($request->invoice_id);
    }


    public function generateEwayBill(Request $request)
    {
        try {
            $invoiceId = $request->invoice_id;
            $invoice = DB::table('stock_outward_mst as a')
                ->select(
                    "a.*",
                    "b.gst"
                )
                ->join("customers as b", "a.customer_id", "b.id")
                ->where('a.id', $invoiceId)
                ->first();
            if (!$invoice || !$invoice->Irn) {
                return response()->json([
                    'status' => false,
                    'message' => 'E-Invoice not generated yet'
                ]);
            }
            $auth = Http::post('https://sandb-api.mastersindia.co/api/v1/token-auth/', [
                "username" => "shubhamgoyal@bulkbasketindia.com",
                "password" => "Basketindia@123",
            ]);

            $authData = $auth->json();

            if (!isset($authData['token'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token generation failed',
                    'error' => $authData
                ]);
            }
            $token = $authData['token'];
            $products = DB::table('stock_outward_det as a')
                ->select(
                    'p.name as product_name',
                    'p.hsn_code',
                    'a.qty',
                    'pu.name as p_unit',
                    'a.price as unit_price',
                    'p.gst'
                )
                ->join('products as p', 'a.product_id', '=', 'p.id')
                ->join('product_uom as pu', 'p.uom_id', '=', 'pu.id')
                ->where('a.mst_id', $invoice->id)
                ->get();
            $itemList = [];
            $gstin = $invoice->gst ?? '';
            $stateCode = substr($gstin, 0, 2);
            foreach ($products as $product) {
                $qty   = (float) $product->qty;
                $price = (float) $product->unit_price;
                $gst   = (float) ($product->gst ?? 0);
                $hsn = preg_replace('/[^0-9]/', '', $product->hsn_code);
                $hsn = substr($hsn, 0, 8);
                $assessableValue = round($price * $qty, 2);
                $igst = 0;
                $cgst = 0;
                $sgst = 0;

                if ($stateCode == "03") {
                    $cgst = round(($assessableValue * $gst) / 200, 2);
                    $sgst = round(($assessableValue * $gst) / 200, 2);
                } else {
                    $igst = round(($assessableValue * $gst) / 100, 2);
                }
                $itemList = [];

                foreach ($products as $product) {

                    $qty   = (float) $product->qty;
                    $product_unit   = $product->p_unit;
                    $price = (float) $product->unit_price;
                    $gst   = (float) ($product->gst ?? 0);

                    $hsn = preg_replace('/[^0-9]/', '', $product->hsn_code);
                    $hsn = substr($hsn, 0, 8);

                    if (!$hsn || strlen($hsn) < 4 || $qty <= 0 || $price <= 0) {
                        continue;
                    }

                    $productName = trim($product->product_name);
                    if (!$productName) {
                        continue;
                    }

                    $taxable = round($qty * $price, 2);

                    $cgst = 0;
                    $sgst = 0;
                    $igst = 0;

                    if ($stateCode == "03") {
                        $cgst = round(($taxable * $gst) / 200, 2);
                        $sgst = round(($taxable * $gst) / 200, 2);
                    } else {
                        $igst = round(($taxable * $gst) / 100, 2);
                    }

                    if (!$hsn || strlen($hsn) < 4 || $qty <= 0 || $price <= 0) {
                        continue;
                    }

                    $taxable = round($qty * $price, 2);

                    $itemList[] = [
                        "product_name"   => trim($product->product_name),
                        "product_description" => trim($product->product_name),
                        "hsn_code"       => $hsn,
                        "quantity"       => $qty,
                        "unit_of_product" => $product->p_unit ?? "NOS",
                        "igst_rate" => 0,
                        "cgst_rate" => $gst / 2,
                        "sgst_rate" => $gst / 2,
                        "cess_rate"      => 0,
                        "cessNonAdvol"   => 0,
                        "taxable_amount" => $taxable
                    ];
                }
            }
            if (empty($itemList)) {
                dd("ItemList empty", $products);
            }
            $payload = [
                "access_token" => $token,
                "userGstin" => "05AAABC0181E1ZE",
                "supply_type" => "outward",
                "sub_supply_type" => "Supply",
                "sub_supply_description" => "Sales",

                "document_type" => "Tax Invoice",
                "document_number" => substr($invoice->invoice_id, 0, 16),
                "document_date" => now()->format('d/m/Y'),

                "gstin_of_consignor" => "05AAABC0181E1ZE",
                "gstin_of_consignee" => "05AAABB0639G1Z8",

                // ✅ STATE FIX
                "state_of_consignor" => "UTTARAKHAND",
                "state_of_supply" => "UTTARAKHAND",

                "actual_from_state_name" => "UTTARAKHAND",
                "actual_to_state_name"   => "UTTARAKHAND",

                "pincode_of_consignor" => 248001,
                "pincode_of_consignee" => 248002,
                "transaction_type" => 1,
                "transportation_mode" => "road",
                "transportation_distance" => "10",

                // "vehicle_number" => "UK07AB1234",
                "vehicle_number" => $invoice->vehicle_number,
                "vehicle_type" => "Regular",

                "itemList" => $itemList
            ];
            // dd(json_encode($payload, JSON_PRETTY_PRINT));
            $response = Http::withHeaders([
                'Authorization' => 'JWT ' . $token,
                'Content-Type' => 'application/json'
            ])->post(
                'https://clientbasic.mastersindia.co/ewayBillsGenerate',
                $payload
            );
            $data = $response->json();
            $eway = $data['results']['message'] ?? [];
            if (empty($eway['ewayBillNo'])) {
                return response()->json([
                    'status' => false,
                    'message' => $data['message'] ?? 'E-Way Bill failed',
                    'error' => $data
                ]);
            }
            $ewayBillDate = null;
            $validUpto = null;

            if (!empty($eway['ewayBillDate'])) {
                $ewayBillDate = Carbon::createFromFormat(
                    'd/m/Y h:i:s A',
                    $eway['ewayBillDate']
                )->format('Y-m-d H:i:s');
            }

            if (!empty($eway['validUpto'])) {
                $validUpto = Carbon::createFromFormat(
                    'd/m/Y h:i:s A',
                    $eway['validUpto']
                )->format('Y-m-d H:i:s');
            }
            DB::table('stock_outward_mst')
                ->where('id', $request->invoice_id)
                ->update([
                    "is_e_billing" => 1,
                    "eway_bill_no" => $eway['ewayBillNo'] ?? null,
                    "eway_bill_date" => $ewayBillDate ?? null,
                    "eway_valid_upto" => $validUpto ?? null,
                    "eway_bill_url" => $eway['url'] ?? null,
                ]);

            return response()->json([
                'status' => true,
                'message' => 'E-Way Bill processed successfully',
                'data' => $data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}
