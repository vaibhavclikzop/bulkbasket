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
            $invoice = DB::table('stock_outward_mst')
                ->where('invoice_id', $request->invoice_id)
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
            $products = DB::table('stock_inward_det as a')
                ->select(
                    'p.name as product_name',
                    'p.hsn_code',
                    'a.qty',
                    'a.price as unit_price',
                    'p.gst'
                )
                ->join('products as p', 'a.product_id', '=', 'p.id')
                ->where('a.mst_id', $request->invoice_id)
                ->get(); 
            $itemList = [];
            foreach ($products as $product) {

                $hsn = substr($product->hsn_code, 0, 8);

                $itemList[] = [
                    "product_name" => $product->product_name ?? 'Product',
                    "hsn_code" => $hsn,
                    "quantity" => (float) ($product->qty ?? 1),
                    "unit_price" => (float) ($product->unit_price ?? 0),
                    "taxable_amount" => (float) ($product->qty * $product->unit_price),
                    "gst_rate" => (float) ($product->gst ?? 0),
                ];
            }
            $payload = [
                "Irn" => $invoice->Irn,
                "itemList" => $itemList,

                "transporter_id" => "05AAABC0181E1ZE",
                "transporter_name" => "ABC Logistics",

                "transport_mode" => "1",
                "transport_doc_no" => "DOC" . rand(1000, 9999),
                "transport_doc_date" => now()->format('d/m/Y'),

                "vehicle_no" => "UK07AB1234",
                "vehicle_type" => "R"
            ];
            $response = Http::withHeaders([
                'Authorization' => 'JWT ' . $token,
                'Content-Type' => 'application/json'
            ])->post(
                'https://sandb-api.mastersindia.co/api/v1/generate-eway-bill/',
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
