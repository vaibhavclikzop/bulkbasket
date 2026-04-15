<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EstimateController extends Controller
{
    public function createEstimate(Request $request)
    {
        $data = DB::table('customers')->where("supplier_id", $request->user["supplier_id"])->where("active", 1)->get();
        return view("suppliers.create-estimate", compact("data"));
    }

    public function getCustomerAddress($id)
    {
        $customer = DB::table("customers")
            ->where("id", $id)
            ->first();
        if (!$customer) {
            return response()->json([
                "status" => false,
                "message" => "Customer not found"
            ]);
        }
        return response()->json([
            "status" => true,
            "wallet" => $customer->wallet,
            "used_wallet" => $customer->used_wallet,
            "hold_amount" => $customer->hold_amount,
            "active_amount" => (round($customer->wallet - $customer->used_wallet - $customer->hold_amount, 2)),
            "address" => $customer->address,
            "state" => $customer->state,
            "district" => $customer->district,
            "city" => $customer->city,
        ]);
    }

    public function saveEstimate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_date' => 'required',
            'address' => 'required',
            'state' => 'required',
            'district' => 'required',
            'city' => 'required',
            "customer_id" => 'required',
            'pay_mode' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first()
            ], 422);
        }
        $challan_data = DB::table("suppliers")->where('id', 1)->first();
        $current_order_id = $challan_data->order_id;
        $next_order_id = $current_order_id + 1;
        $order_id = $challan_data->order_series . $next_order_id;
        DB::beginTransaction();
        try {
            $prod_list = json_decode($request->prod_list);
            if (!$prod_list || count($prod_list) == 0) {
                return redirect()->back()->with('error', "Select at least one product");
            }
            $products = [];
            foreach ($prod_list as $item) {
                $product = DB::table("products")
                    ->where("id", $item->product_id)
                    ->first();
                if (!$product) continue;
                $price = $item->price;
                $tiers = DB::table("product_price")
                    ->where("product_id", $item->product_id)
                    ->orderBy("qty", "asc")
                    ->get();

                foreach ($tiers as $tier) {
                    if ($item->qty >= $tier->qty) {
                        $price = $tier->price;
                    }
                }
                $products[] = (object)[
                    "supplier_id" => $product->supplier_id,
                    "product_id" => $product->id,
                    "name" => $product->name,
                    "description" => $product->description,
                    "qty" => $item->qty,
                    "price" => $price,
                    "gst" => $product->gst,
                    "cess_tax" => $product->cess_tax,
                ];
            }
            $grouped = collect($products)->groupBy("supplier_id");
            $customer = DB::table("customers")->where("id", $request->customer_id)->first();
            $total_amount = 0;
            $invoice_no = 'INV-' . $request->customer_id . date('YmdHis');
            $order_id = DB::table("order_estimate")->insertGetId([
                "order_id" => $order_id,
                "customer_id" => $request->customer_id,
                "invoice_no" => $invoice_no,
                "pay_mode" => $request->pay_mode,
                "payment_status" => "Pending",
                "order_status" => "Pending",
                "total_amount" => 0,
                "name" => $customer->name,
                "number" => $customer->number,
                "email" => $customer->email,
                "address" => $request->address,
                "state" => $request->state,
                "district" => $request->district,
                "city" => $request->city,
                "pincode" => $customer->pincode,
                "remarks" => $request->remarks ?? null,
                "delivery_date" => $request->delivery_date,
            ]);
            foreach ($grouped as $supplier_id => $items) {
                $supplierSubtotal = 0;
                $gst_total = 0;
                $cess_total = 0;
                foreach ($items as $item) {
                    $rowTotal = $item->price * $item->qty;
                    $rowGst = ($rowTotal * $item->gst) / 100;
                    $rowCess = ($rowTotal * $item->cess_tax) / 100;
                    $supplierSubtotal += $rowTotal;
                    $gst_total += $rowGst;
                    $cess_total += $rowCess;
                    DB::table("order_estimate_item")->insert([
                        "supplier_id" => $supplier_id,
                        "order_id" => $order_id,
                        "product_id" => $item->product_id,
                        "qty" => $item->qty,
                        "price" => $item->price,
                        "cess_tax" => $item->cess_tax,
                        "gst" => $item->gst,
                        "name" => $item->name,
                        "description" => $item->description,
                    ]);
                }
                $finalSupplierTotal = $supplierSubtotal + $gst_total + $cess_total;
                DB::table("orders_supplier")->insert([
                    "order_id" => $order_id,
                    "supplier_id" => $supplier_id,
                    "subtotal" => $finalSupplierTotal,
                    "shipping_status" => "pending",
                ]);
                $total_amount += $finalSupplierTotal;
            }
            DB::table('order_estimate')->where('id', $order_id)->update([
                'total_amount' => $total_amount
            ]);
            if ($request->pay_mode === 'wallet') {
                $wallet = (float)($customer->wallet ?? 0);
                $holdAmount = (float)($customer->hold_amount ?? 0);
                $usedWallet = (float)($customer->used_wallet ?? 0);
                if (($holdAmount + $usedWallet + $total_amount) > $wallet) {
                    DB::rollBack();
                    return redirect()->with('error', 'Wallet amount is less than order total.');
                }
                DB::table('order_estimate')->where('id', $order_id)->update([
                    'payment_status' => "Hold"
                ]);
                DB::table("customers")
                    ->where("id", $request->customer_id)
                    ->increment("hold_amount", $total_amount);
            }
            DB::commit();
            DB::table("suppliers")
                ->where('id', 1)
                ->update([
                    'order_id' => $next_order_id
                ]);
            return redirect()->back()->with('success', "Estimate Saved Successfully");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
