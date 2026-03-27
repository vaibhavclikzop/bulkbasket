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

        DB::beginTransaction();

        $prod_list = json_decode($request->prod_list);
        if (!$prod_list) {
            return redirect()->back()->with('error', "Select at least one product");
        }
        foreach ($prod_list as $key => $value) {

            DB::table("cart")->insert(array(
                "product_id" => $value->product_id,
                "qty" => $value->qty,
                "customer_id" => $request->customer_id,
            ));
        }

        try {
            $cart = DB::table("cart as a")
                ->select("a.*", "b.supplier_id", "b.base_price as mrp", "b.name as product", "b.description", "b.cess_tax", "b.gst")
                ->join("products as b", "a.product_id", "=", "b.id")
                ->where("a.customer_id", $request->customer_id)
                ->get()
                ->groupBy("supplier_id");

            if ($cart->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart is empty.'
                ], 400);
            }

            // Tier pricing
            foreach ($cart as $k => $v) {
                foreach ($v as $item) {
                    $tiers = DB::table("product_price")
                        ->where("product_id", $item->product_id)
                        ->orderBy("qty", "asc")
                        ->get();

                    foreach ($tiers as $tier) {
                        if ($item->qty >= $tier->qty) {
                            $item->mrp = $tier->price;
                        }
                    }
                }
            }

            $customer = $request->delivery_address === "Office"
                ? DB::table("customers")->where("id", $request->customer_id)->first()
                : DB::table("customer_users")->where("id", $request->user["id"])->first();

            $total_amount = 0;
            $invoice_no = 'INV-' . $request->customer_id . date('YmdHis');

            $order_id = DB::table("order_estimate")->insertGetId([
                "customer_id" => $request->customer_id,
                "invoice_no" => $invoice_no,
                "pay_mode" => $request->pay_mode,
                "payment_status" => "Pending",
                "order_status" => "Pending",
                "total_amount" => $total_amount,
                "name" => $request->name ?? $customer->name,
                "number" => $request->delivery_phone ?? $customer->number,
                "email" => $customer->email,
                "address" => $request->address ?? $customer->address,
                "state" => $request->state ?? $customer->state,
                "district" => $request->district ?? $customer->district,
                "city" => $request->city ?? $customer->city,
                "pincode" => $request->pincode ?? $customer->pincode,
                "remarks" => $request->remarks ?? null,
                "delivery_date" => $request->delivery_date ?? null,
            ]);

            foreach ($cart as $supplier_id => $items) {
                $supplierSubtotal = $items->sum(fn($item) => $item->mrp * $item->qty);

                $orderSupplierId = DB::table("orders_supplier")->insertGetId([
                    "order_id" => $order_id,
                    "supplier_id" => $supplier_id,
                    "subtotal" => $supplierSubtotal,
                    "shipping_status" => "pending",
                ]);

                $gst_total = 0;
                $cess_total = 0;

                foreach ($items as $item) {
                    DB::table("order_estimate_item")->insert([
                        "supplier_id" => $supplier_id,
                        "order_id" => $order_id,
                        "product_id" => $item->product_id,
                        "qty" => $item->qty,
                        "price" => $item->mrp,
                        "cess_tax" => $item->cess_tax,
                        "gst" => $item->gst,
                        "name" => $item->product,
                        "description" => $item->description,
                    ]);

                    $gst_total += $item->mrp * $item->qty * $item->gst / 100;
                    $cess_total += $item->mrp * $item->qty * $item->cess_tax / 100;
                }

                DB::table("orders_supplier")->where("id", $orderSupplierId)->update([
                    "subtotal" => $supplierSubtotal + $gst_total + $cess_total,
                ]);

                $total_amount += $supplierSubtotal + $gst_total + $cess_total;
            }

            DB::table('order_estimate')->where('id', $order_id)->update([
                'total_amount' => $total_amount
            ]);
            $customer = DB::table("customers")->where("id", $request->customer_id)->first();

            if ($request->pay_mode === 'wallet') {

                $wallet = (float)($customer->wallet ?? 0);
                $holdAmount = (float)($customer->hold_amount ?? 0);
                $usedWallet = (float)($customer->used_wallet ?? 0);

                if (($holdAmount + $usedWallet + $total_amount) > $wallet) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Wallet amount is less than order total.'
                    ], 400);
                }

                DB::table('order_estimate')->where('id', $order_id)->update([
                    'payment_status' => "Hold"
                ]);

                DB::table("customers")
                    ->where("id", $request->customer_id)
                    ->increment("hold_amount", $total_amount);
            }

            DB::table("cart")
                ->where("customer_id", $request->customer_id)
                ->delete();

            DB::commit();

            return redirect()->back()->with('success', "Save Successfully");
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
        return redirect()->back()->with('success', "Save Successfully");
    }
}
