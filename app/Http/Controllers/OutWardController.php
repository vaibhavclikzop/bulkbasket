<?php

namespace App\Http\Controllers;

use App\Models\WareHouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OutWardController extends Controller
{
    public function OutwardStock(Request $request)
    {
        $customers = DB::table("customer_users as cu")
            ->join("customers as c", "cu.customer_id", "=", "c.id")
            ->where("c.active", 1)
            ->select("cu.*", "cu.name as customer_name")
            ->get();
        $order_mst = DB::table("orders")
            ->where("customer_id", $request->customer_id)
            ->whereNot("order_status", "complete")
            ->get();
        $warehouse =   DB::table("warehouse")->get();
        $out_id = request("out_id");
        $outward_mst = null;
        $outward_det = null;
        if (request("out_id")) {
            $id = request("out_id");
            $outward_mst = DB::table("stock_outward_mst")
                ->where("id", $id)
                ->first();
            $outward_det = DB::table("stock_outward_det as a")
                ->select(
                    "a.*",
                    "b.name as product",
                    "b.article_no as article_no",
                    "c.out_qty",
                    "c.qty",
                    "z.name as brand",
                    "l.location_code",
                    DB::raw("IFNULL(j.stock,0) as stock"),
                    "a.qty as outward_qty"
                )
                ->join("products as b", "a.product_id", "b.id")
                ->join("orders_item as c", function ($join) use ($outward_mst) {
                    $join->on("a.product_id", "=", "c.product_id")
                        ->where("c.order_id", "=", $outward_mst->order_id);
                })
                ->join("product_brand as z", "b.brand_id", "z.id")
                ->leftJoin("current_stock as j", function ($join) {
                    $join->on("a.product_id", "=", "j.product_id")
                        ->on("a.location_id", "=", "j.location_id");
                })
                ->leftJoin("warehouse_location as l", "a.location_id", "=", "l.id")
                ->where("a.mst_id", $id)
                ->get();
        }
        return view("suppliers.outward-stock", compact("customers", "order_mst", "warehouse",  "outward_mst", "outward_det"));
    }

    public function GetCustomerOrder(Request $request)
    {
        $order_mst =  DB::table("orders as a")
        ->select('a.*','oe.order_id as e_order_id')
        ->join("order_estimate as oe", "a.estimate_id", "=", "oe.id")
        ->where("a.customer_id", $request->id)
            ->whereNot("a.order_status", "complete")
            ->get();
        return $order_mst;
    }

    public function GetOrderDet(Request $request)
    {
        $order_det = DB::table("orders_item as a")
            ->select(
                "a.id",
                "a.product_id",
                "a.qty",
                "a.price",
                "a.out_qty",
                "b.name as product",
                "b.article_no as article_no",
                "z.name as brand",
                "l.id as location_id",
                "l.location_code",
                DB::raw("IFNULL(SUM(d.stock),0) as current_stock")
            )
            ->leftJoin("products as b", "a.product_id", "=", "b.id")
            ->leftJoin("orders as c", "a.order_id", "=", "c.id")
            ->leftJoin("product_brand as z", "b.brand_id", "=", "z.id")
            ->leftJoin("current_stock as d", function ($join) use ($request) {
                $join->on("a.product_id", "=", "d.product_id")
                    ->where("d.warehouse_id", "=", $request->warehouse_id);
            })
            ->leftJoin("warehouse_location as l", "d.location_id", "=", "l.id")
            ->where("a.order_id", $request->id)
            // ->whereRaw("a.qty > IFNULL(a.out_qty,0)")
            ->groupBy(
                "a.id",
                "a.product_id",
                "a.qty",
                "a.price",
                "a.out_qty",
                "b.name",
                "b.article_no",
                "z.name",
                "l.id",
                "l.location_code"
            )
            ->get();
        return $order_det;
    }

    public function SaveOutwardStock(Request $request)
    {
        $outward_id = 'PT_' . date('dmyhis');
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'order_id' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->errors();
            $count = 0;
            foreach ($messages->all() as $error) {
                if ($count == 0)
                    return redirect()->back()->with('error', $error);
                $count++;
            }
        }
        $prod_list = json_decode($request->prod_list);
        if (!$prod_list) {
            return redirect()->back()->with('error', "Select at least one product");
        }
        DB::beginTransaction();
        $order_mst =  DB::table("orders")->where("id", $request->order_id)->first();
        try {
            if (!$request->id) {
                $mst_id = DB::table('stock_outward_mst')->insertGetId(array(
                    "customer_id" => $request->customer_id,
                    "order_id" => $request->order_id,
                    "outward_id" => $outward_id,
                    "supplier_id" => $request->user['supplier_id'],
                    "warehouse_id" => $request->warehouse_id,
                ));
            } else {
                DB::table('stock_outward_mst')->where("id", $request->id)->update(array(
                    "description" => $request->description,
                ));
                $mst_id = $request->id;
                $som = DB::table("stock_outward_mst")->where("id", $mst_id)->first();
                $sod = DB::table("stock_outward_det")->where("mst_id", $mst_id)->get();
                foreach ($sod as $key => $value) {
                    DB::table("orders_item")
                        ->where("order_id", $som->order_id)
                        ->where("product_id", $value->product_id)
                        ->decrement("out_qty", $value->qty);
                    DB::table('current_stock')
                        ->where("warehouse_id", $value->warehouse_id)
                        ->where("location_id", $value->location_id)
                        ->where("product_id", $value->product_id)
                        ->update([
                            "stock" => DB::raw("stock - $value->qty")
                        ]);
                    DB::table("stock_outward_det")->where("id", $value->id)->delete();
                }
            }
            $status = 0;
            foreach ($prod_list as $key => $value) {

                if ($value->qty <= 0) {
                    continue;
                }
                $stock = DB::table("stock_outward_det")
                    ->where("mst_id", $mst_id)
                    ->where("product_id", $value->product_id)
                    ->first();

                if ($stock) {
                    $qty = $value->qty - $stock->qty;
                    DB::table("stock_outward_det")
                        ->where("id", $stock->id)
                        ->update([
                            "warehouse_id" => $request->warehouse_id,
                            "location_id" => $value->location_id,
                            "qty" => $value->qty,
                            "price" => $value->price,
                        ]);
                } else {
                    $qty = $value->qty;
                    DB::table('stock_outward_det')->insert([
                        "mst_id" => $mst_id,
                        "warehouse_id" => $request->warehouse_id,
                        "location_id" => $value->location_id,
                        "product_id" => $value->product_id,
                        "qty" => $value->qty,
                        "price" => $value->price,
                    ]);
                }
                DB::table("orders_item")
                    ->where("order_id", $request->order_id)
                    ->where("product_id", $value->product_id)
                    ->increment("out_qty", $qty);
                DB::table('current_stock')
                    ->where("warehouse_id", $request->warehouse_id)
                    ->where("location_id", $value->location_id)
                    ->where("product_id", $value->product_id)
                    ->update([
                        "stock" => DB::raw("GREATEST(stock - $qty,0)")
                    ]);
            }
            $pending = DB::table('orders_item')
                ->where("order_id", $request->order_id)
                ->whereRaw("qty > out_qty")
                ->exists();
            if ($pending) {
                DB::table('orders')
                    ->where("id", $request->order_id)
                    ->update([
                        "status" => "pending"
                    ]);
            } else {
                DB::table('orders')
                    ->where("id", $request->order_id)
                    ->update([
                        "status" => "complete"
                    ]);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
        return redirect()->back()->with('success', "Save Successfully");
    }

    public function OutwardOrderList(Request $request)
    {
        $status = request()->status;
        $id = request()->id;
        $out = DB::table("stock_outward_mst as a")
            ->select(
                "a.*",
                "c.name as customer_name",
                "b.status as outward_status",
                "oe.order_id as e_order_id",
                DB::raw("
                (
                    SELECT SUM(
                        (d.price * d.qty) + ((d.price * d.qty * p.gst) / 100)
                    )
                    FROM stock_outward_det as d
                    JOIN products as p ON p.id = d.product_id
                    WHERE d.mst_id = a.id
                ) as invoice_amount
            ")
            )
            ->join("orders as b", "a.order_id", "=", "b.id")
            ->join("order_estimate as oe", "b.estimate_id", "=", "oe.id")
            ->leftJoin("customer_users as c", "b.customer_id", "=", "c.id")
            ->where("a.supplier_id", $request->user['supplier_id']);
        if ($id) {
            $out->where("a.order_id", $id);
        }
        if ($status == "complete") {
            $out->where("a.is_invoice", 1);
        } else {
            $out->where("a.status", $status)
                ->where("a.is_invoice", 0);
        }
        $outward = $out->orderBy("a.id", "desc")->get();
        return view("suppliers.outward-order-list", compact("outward"));
    }


    public function OutwardChallanView(Request $request, $id)
    {
        $data =  DB::table("stock_outward_mst as a")
            ->select("a.*", "d.name as supplier_name", "d.address as supplier_address", "d.number as supplier_number", "d.email as supplier_email", "d.gst as supplier_gst", "c.name as customer_name", "c.address", "c.state", "c.city", "c.pincode", "c.email", "c.number", "c.gst", "b.delivery_date", "b.address as delivery_address", "b.delivery_date as delivery_date", "b.state as delivery_state")
            ->join("orders as b", "a.order_id", "b.id")
            ->join("customers as c", "b.customer_id", "c.id")
            ->join("suppliers as d", "a.supplier_id", "d.id")
            ->where("a.id", $id)
            ->first();
        $order_det = DB::table("stock_outward_det as a")
            ->select("a.*", "b.name as product",  "b.article_no", "e.name as brand", "b.hsn_code")
            ->join("products as b", "a.product_id", "=", "b.id")
            ->join("stock_outward_mst as c", "a.mst_id", "=", "c.id")
            ->join("orders as d", "c.order_id", "=", "d.id")
            ->join("product_brand as e", "b.brand_id", "=", "e.id")
            ->where("a.mst_id", $id)
            ->get();
        $nextProduct = DB::table("stock_outward_mst")
            ->where("id", ">", $id)
            ->orderBy("id", "asc")
            ->first();
        // Get the previous record
        $previousProduct = DB::table("stock_outward_mst")
            ->where("id", "<", $id)
            ->orderBy("id", "desc")
            ->first();
        return view("suppliers.outward-challan-view", compact("data", "order_det", "nextProduct", "previousProduct"));
    }

    public function invoiceView(Request $request, $id)
    {
        $data =  DB::table("stock_outward_mst as a")
            ->select("a.*", "d.name as supplier_name", "d.address as supplier_address", "d.number as supplier_number", "d.email as supplier_email", "d.gst as supplier_gst", "c.name as customer_name", "c.address", "c.state", "c.city", "c.pincode", "c.email", "c.number", "c.gst", "b.delivery_date", "b.address as delivery_address", "b.delivery_date as delivery_date", "b.state as delivery_state")
            ->join("orders as b", "a.order_id", "b.id")
            ->join("customers as c", "b.customer_id", "c.id")
            ->join("suppliers as d", "a.supplier_id", "d.id")
            ->where("a.id", $id)
            ->first();
        $order_det = DB::table("stock_outward_det as a")
            ->select("a.*", "b.name as product",  "b.article_no", "e.name as brand", "b.hsn_code", "b.gst", "b.cess_tax")
            ->join("products as b", "a.product_id", "=", "b.id")
            ->join("stock_outward_mst as c", "a.mst_id", "=", "c.id")
            ->join("orders as d", "c.order_id", "=", "d.id")
            ->leftJoin("product_brand as e", "b.brand_id", "=", "e.id")
            ->where("a.mst_id", $id)
            ->get();
        $gst = DB::table("product_gst")->get();
        return view("suppliers.invoice-view", compact("data", "order_det", "gst"));
    }

    public function convertToInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            $messages = $validator->errors();
            $count = 0;
            foreach ($messages->all() as $error) {
                if ($count == 0)
                    return redirect()->back()->with('error', $error);
                $count++;
            }
        }
        try {
            $inv = 'INV-' . date('YmdHis') . '-' . rand(100, 999);
            DB::table('stock_outward_mst')->where("id", $request->id)->update(array(
                "is_invoice" => 1,
                "invoice_id" => $inv,
            ));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function invoices(Request $request)
    {
        $status = request()->status;
        $id = request()->id;
        $out = DB::table("stock_outward_mst as a")
            ->select(
                "a.id",
                "a.order_id",
                "a.outward_id",
                "a.invoice_id",
                "oe.order_id",
                "a.status",
                "a.is_invoice",
                "a.is_e_invoice",
                "a.EinvoicePdf",
                "a.dispatch_status",
                "a.is_e_billing",
                "a.eway_bill_url",
                "a.created_at",
                "c.name as customer_name",
                DB::raw("SUM((d.qty * d.price) 
            + ((d.qty * d.price) * p.gst / 100)
            + ((d.qty * d.price) * p.cess_tax / 100)
        ) as total_amount")
            )
            ->leftJoin("orders as b", "a.order_id", "=", "b.id")
            ->leftJoin("order_estimate as oe", "b.estimate_id", "=", "oe.id")
            ->leftJoin("customer_users as c", "b.customer_id", "=", "c.id")
            ->leftJoin("stock_outward_det as d", "a.id", "=", "d.mst_id")
            ->leftJoin("products as p", "d.product_id", "=", "p.id")
            ->where("a.supplier_id", $request->user['supplier_id'])
            ->where("a.is_invoice", 1)
            ->groupBy(
                "a.id",
                "a.order_id",
                "a.outward_id",
                "a.invoice_id",
                "a.status",
                "a.EinvoicePdf",
                "a.is_invoice",
                "a.is_e_invoice",
                "a.dispatch_status",
                "a.is_e_billing",
                "oe.order_id",
                "a.eway_bill_url",
                "a.created_at",
                "c.name"
            );
        $outward = $out
            ->orderBy("a.id", "desc")
            ->get();
            // dd($outward);
        return view("suppliers.invoices", compact("outward"));
    }

    public function cancelOutwardChallan(Request $request)
    {
        DB::beginTransaction();

        try {

            $som = DB::table("stock_outward_mst")
                ->where("id", $request->id)
                ->first();

            if (!$som) {
                return redirect()->back()->with('error', 'Invalid Challan');
            }

            $sod = DB::table("stock_outward_det")
                ->where("mst_id", $request->id)
                ->get();

            foreach ($sod as $value) {

                // 🔥 reduce outward qty
                DB::table("orders_item")
                    ->where("order_id", $som->order_id)
                    ->where("product_id", $value->product_id)
                    ->decrement("out_qty", $value->qty);

                // 🔥 stock return
                DB::table('current_stock')
                    ->where("warehouse_id", $value->warehouse_id)
                    ->where("location_id", $value->location_id)
                    ->where("product_id", $value->product_id)
                    ->update([
                        "stock" => DB::raw("stock + {$value->qty}")
                    ]);
            }
            DB::table("stock_outward_det")
                ->where("mst_id", $request->id)
                ->delete();
            DB::table("stock_outward_mst")
                ->where("id", $request->id)
                ->delete();
            $pending = DB::table('orders_item')
                ->where("order_id", $som->order_id)
                ->whereRaw("qty > out_qty")
                ->exists();

            DB::table("orders")
                ->where("id", $som->order_id)
                ->update([
                    "order_status" => $pending ? "processing" : "complete"
                ]);

            DB::commit();

            return redirect()->back()->with("success", "Challan Cancel & Deleted Successfully");
        } catch (\Throwable $th) {

            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function DispatchChallan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            $messages = $validator->errors();
            $count = 0;
            foreach ($messages->all() as $error) {
                if ($count == 0)
                    return redirect()->back()->with('error', $error);
                $count++;
            }
        }
        try {

            $mst_id = DB::table('stock_outward_mst')->where("id", $request->id)->update(array(
                "dispatch_status" => "processing",

            ));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
        return  redirect()->back()->with("success", "Save Successfully");
    }
}
