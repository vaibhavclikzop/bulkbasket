<?php

namespace App\Http\Controllers\eInvoice;

use App\Http\Controllers\Controller;
use App\Services\EInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

                    $itemList[] = [
                        "productName"   => $productName,
                        "hsnCode"       => $hsn,
                        "quantity"      => $qty,
                        "qtyUnit"       => $product_unit,
                        "taxableAmount" => $taxable,

                        "cgstRate"      => $stateCode == "03" ? (int)($gst / 2) : 0,
                        "sgstRate"      => $stateCode == "03" ? (int)($gst / 2) : 0,
                        "igstRate"      => $stateCode != "03" ? (int)$gst : 0,

                        "cgstAmount"    => $cgst,
                        "sgstAmount"    => $sgst,
                        "igstAmount"    => $igst,
                        "cessRate"      => 0,
                        "cessAmount"    => 0
                    ];
                }
            }
            if (empty($itemList)) {
                dd("ItemList empty", $products);
            }
            $payload = [
                "Irn" => $invoice->Irn,

                "supply_type" => "outward",
                "document_type" => "tax invoice",
                "sub_supply_type" => "Others",
                "document_number" => $invoice->invoice_id,
                "document_date" => now()->format('d/m/Y'), 
                "gstin_of_consignor" => "05AAAAZ2166M1Z7",
                "pincode_of_consignor" => "248001",
                "state_of_consignor" => "05",    

                "gstin_of_consignee" => $invoice->gst,
                "pincode_of_consignee" => "110001",
                "state_of_supply" => "02",      

                "itemList" => $itemList,

                "transporter_id" => "05AAABC0181E1ZE",
                "transporter_name" => "ABC Logistics",
                "transportation_mode" => "road",  
                "transportation_distance" => "100",

                "vehicle_number" => "UK07AB1234",  
                "vehicle_type" => "Regular"       
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
            $isSuccess =
                ($data['success'] ?? null) === true ||
                ($data['status'] ?? null) === 'Success' ||
                ($data['results']['status'] ?? null) === 'Success';
            if (!$isSuccess) {
                $msg =
                    $data['data']['message'] ??
                    $data['message'] ??
                    $data['error']['errorMessage'] ??
                    $data['results']['errorMessage'] ??
                    'E-Way Bill failed';
                return response()->json([
                    'status' => false,
                    'message' => $msg,
                    'error' => $data
                ]);
            }
            DB::table('stock_outward_mst')
                ->where('invoice_id', $request->invoice_id)
                ->update([
                    'eway_bill_no' => $data['results']['eway_bill_no'] ?? null,
                    'eway_bill_date' => $data['results']['eway_bill_date'] ?? null,
                    'eway_valid_upto' => $data['results']['valid_upto'] ?? null,
                ]);
            return response()->json([
                'status' => true,
                'message' => 'E-Way Bill generated successfully',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}
