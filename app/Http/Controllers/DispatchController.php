<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Validator;

class DispatchController extends Controller
{

    public function ModeOfTransport(Request $request)
    {
        $data = DB::table("mode_of_transport")->get();
        return view("suppliers.mode-of-transport", compact("data"));
    }

    public function saveModeOfTransport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'number' => 'required',
            'vehicle_no' => 'required',
            'vehicle_name' => 'required',
            'user_name' => 'required',
            'password' => 'required',
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
            if (empty($request->id)) {
                DB::table('mode_of_transport')->insertGetId(array(
                    "name" => $request->name,
                    "number" => $request->number,
                    "vehicle_no" => $request->vehicle_no,
                    "vehicle_name" => $request->vehicle_name,
                    "user_name" => $request->user_name,
                    "password" => $request->password,
                    "supplier_id" => $request->user['supplier_id'],
                ));
            } else {
                DB::table('mode_of_transport')->where("id", $request->id)->update(array(
                    "name" => $request->name,
                    "number" => $request->number,
                    "vehicle_no" => $request->vehicle_no,
                    "vehicle_name" => $request->vehicle_name,
                    "user_name" => $request->user_name,
                    "password" => $request->password,
                ));
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        return  redirect()->back()->with("success", "Save Successfully");
    }


    public function dispatchPlan(Request $request, $status)
    {
        // $status = "processing";
        $id = request()->id;
        $out = DB::table("stock_outward_mst as a")
            ->select(
                "a.id",
                "a.order_id",
                "a.outward_id",
                "a.invoice_id",
                "a.status",
                "a.is_invoice",
                "oe.order_id",
                "a.is_e_invoice",
                "a.EinvoicePdf",
                "a.dispatch_status",
                "a.is_e_billing",
                "a.eway_bill_url",
                "a.driver_name",
                "a.driver_no",
                "a.vehicle_number",
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
            ->where("a.dispatch_status", $status)
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
                "a.driver_name",
                "a.driver_no",
                "a.vehicle_number",
                "a.created_at",
                "c.name"
            );
        $outward = $out
            ->orderBy("a.id", "desc")
            ->get();
        $transport = DB::table("mode_of_transport")->get();
        return view('suppliers.dispatch-plan', compact("outward", "transport"));
    }

    public function DispatchTransport(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'transport_id' => 'required',
        ]);
        $transport = DB::table('mode_of_transport')
            ->where('id', $request->transport_id)
            ->first();

        if (!$transport) {
            return back()->with('error', 'Transport not found');
        }

        DB::table('stock_outward_mst')
            ->where('id', $request->id)
            ->update([
                // 'dispatch_status' => 'final',
                'status' => 'dispatch',
                'transport_id' => $request->transport_id,
                'transport_date' => $request->transport_date,
                'transport_remarks' => $request->transport_remarks,
                'vehicle_number' => $transport->vehicle_no,
                'driver_name' => $transport->name,
                'driver_no' => $transport->number,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Vehicle allocated successfully');
    }

    public function DispatchOrderStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'status' => 'required'
        ]);

        DB::table('stock_outward_mst')
            ->where('id', $request->order_id)
            ->update([
                'dispatch_status' =>'final',
                'status' => $request->status,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Status  successfully');
    }

     public function orderDelivered(Request $request, $status)
    {
        // $status = "processing";
        $id = request()->id;
        $out = DB::table("stock_outward_mst as a")
            ->select(
                "a.id",
                "a.order_id",
                "a.outward_id",
                "a.invoice_id",
                "a.status",
                "a.is_invoice",
                "a.is_e_invoice",
                "a.EinvoicePdf",
                "a.dispatch_status",
                "a.is_e_billing",
                "a.eway_bill_url",
                "a.vehicle_number",
                "a.driver_name",
                "a.driver_no",
                "a.created_at",
                "c.name as customer_name",
                DB::raw("SUM((d.qty * d.price) 
            + ((d.qty * d.price) * p.gst / 100)
            + ((d.qty * d.price) * p.cess_tax / 100)
        ) as total_amount")
            )
            ->leftJoin("orders as b", "a.order_id", "=", "b.id")
            ->leftJoin("customer_users as c", "b.customer_id", "=", "c.id")
            ->leftJoin("stock_outward_det as d", "a.id", "=", "d.mst_id")
            ->leftJoin("products as p", "d.product_id", "=", "p.id")
            ->where("a.supplier_id", $request->user['supplier_id'])
            ->where("a.is_invoice", 1)
            ->where("a.status", $status)
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
                "a.eway_bill_url",
                "a.vehicle_number",
                "a.driver_name",
                "a.driver_no",
                "a.created_at",
                "c.name"
            );
        $outward = $out
            ->orderBy("a.id", "desc")
            ->get(); 
        $transport = DB::table("mode_of_transport")->get();
        return view('suppliers.delivered-outwards', compact("outward", "transport"));
    }
}
