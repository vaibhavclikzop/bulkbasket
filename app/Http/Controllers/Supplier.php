<?php

namespace App\Http\Controllers;

use App\Models\SupplierRole;
use App\Models\SupplierUsers;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\Eloquent\Casts\Json;
use Jenssegers\Agent\Agent;
use Yajra\DataTables\Facades\DataTables;
use App\Mail\OrderStatusMail;
use App\Mail\OrderEstimateStatusMail;
use App\Models\Vendor;
use App\Models\WarehouseLocation;
use App\Models\WarehouseProductsLocation;
use App\Models\WareHouseZone;
use App\Models\VendorProducts;
use App\Models\Products;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class Supplier extends Controller
{
    public function Dashboard(Request $request)
    {
        $supplierId = $request->user['supplier_id'];

        $totalCustomer = DB::table('customers')
            ->where('active', 1)
            ->where('supplier_id', $supplierId)
            ->count();
        $totalOrders = DB::table('orders')
            ->count();
        $deliveredOrders = DB::table('orders')
            ->where('order_status', 'Delivered')
            ->count();
        $totalEstimates = DB::table('order_estimate')->where("order_status", "Pending")
            ->count();
        $recentEstimateOrder = DB::table('order_estimate as oe')
            ->join('customers as c', 'c.id', '=', 'oe.customer_id')
            ->select(
                'oe.id',
                'oe.payment_status',
                'oe.total_amount',
                'oe.order_status',
                'c.name',
                'c.number'
            )
            ->orderBy('oe.id', 'desc')
            ->where('order_status', 'Pending')
            ->take(10)
            ->get();
        $recentOrder = DB::table('orders as oe')
            ->join('customers as c', 'c.id', '=', 'oe.customer_id')
            ->select(
                'oe.id',
                'oe.payment_status',
                'oe.invoice_no',
                'oe.total_amount',
                'oe.order_status',
                'c.name',
                'c.number'
            )
            ->orderBy('oe.id', 'desc')
            ->take(10)
            ->get();
        $salesData = DB::table('orders')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->where('payment_status', 'Complete')
            ->whereYear('created_at', date('Y'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $salesTotalsArr = array_fill(0, 12, 0);
        foreach ($salesData as $row) {
            $salesTotalsArr[$row->month - 1] = (float)$row->total_sales;
        }
        $salesMonths = json_encode($months);
        $salesTotals = json_encode($salesTotalsArr);
        return view('suppliers.dashboard', compact(
            'totalCustomer',
            'totalOrders',
            'deliveredOrders',
            'totalEstimates',
            'recentOrder',
            'recentEstimateOrder',
            'salesMonths',
            'salesTotals',
        ));
    }

    public function Customers(Request $request, $id)
    {

        $data = DB::table('customers')->where("supplier_id", $request->user['supplier_id'])->orderBy("id", "desc")->where("active", $id)->get();
        return view("suppliers.customers", compact("data"));
    }

    public function SaveCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'number' => 'required|digits:10',
            'name' => 'required',
            'password' => 'required',
            'company_name' => 'required',
            'company_number' => 'required',
            'customer_type' => 'required',
            'type' => 'required',

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

            DB::beginTransaction();
            $customer_id = DB::table('customers')->insertGetId(array(

                "customer_type" => $request->customer_type,
                "type" => $request->type,
                "name" => $request->company_name,
                "number" => $request->company_number,
                "email" => $request->company_email,
                "gst" => $request->company_gst,
                "address" => $request->company_address,
                "state" => $request->company_state,
                "city" => $request->company_city,
                "district" => $request->company_district,
                "pincode" => $request->company_pincode,
                "supplier_id" => $request->user['supplier_id'],

            ));
            DB::table('customer_users')->insertGetId(array(
                "name" => $request->name,
                "number" => $request->number,
                "email" => $request->email,
                "address" => $request->address,
                "state" => $request->state,
                "city" => $request->city,
                "district" => $request->district,
                "pincode" => $request->pincode,
                "password" => $request->password,
                "customer_id" => $customer_id,
            ));

            $documents = DB::table('documents')->where('type', $request->type)->get();

            if ($documents->isNotEmpty()) {
                $customerDocuments = $documents->map(function ($doc) use ($customer_id) {
                    return [
                        'customer_id' => $customer_id,
                        'type'        => $doc->type,
                        'name'        => $doc->name,
                    ];
                })->toArray();

                DB::table('customer_document')->insert($customerDocuments);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function Profile(Request $request)
    {
        $data = DB::table("supplier_users")->where("id", $request->user['id'])->first();
        return view("suppliers.profile", compact("data"));
    }

    public function UpdateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'number' => 'required|digits:10',
            'name' => 'required',
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


            DB::table('supplier_users')->where("id", $request->user['id'])->update(array(

                "name" => $request->name,
                "number" => $request->number,
                "email" => $request->email,

                "address" => $request->address,
                "state" => $request->state,
                "city" => $request->ity,
                "district" => $request->district,
                "pincode" => $request->pincode,
                "password" => $request->password,



            ));
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }


    public function CustomerProfile(Request $request, $id)
    {
        $data = DB::table("customers")->where("id", $id)->first();
        $user = DB::table("customer_users")->where("customer_id", $id)->first();
        $documents = DB::table("customer_document")->where("customer_id", $id)->get();

        // $wallet_statement = DB::table(DB::raw("(
        //     SELECT id, created_at, amount, 'credit' as type, invoice_no, 'Sale (GST)' as particular, pay_date,pay_mode,remarks
        //     FROM wallet_ledger
        //     WHERE customer_id = $id

        //     UNION ALL 

        //     SELECT id, created_at, total_amount as amount, 'debit' as type, invoice_no, 'Payment' as particular, created_at as pay_date,pay_mode, 'Order Generated' as remarks
        //     FROM orders
        //     WHERE customer_id = $id AND pay_mode = 'wallet'
        // ) as wallet_union"))
        //     ->orderBy('created_at', 'asc')
        //     ->get();


        // $balance = 0;
        // foreach ($wallet_statement as $entry) {
        //     if ($entry->type === 'credit') {
        //         $balance += $entry->amount;
        //     } else if ($entry->type === 'debit') {
        //         $balance -= $entry->amount;
        //     }
        //     $entry->balance = -$balance;
        // }

        $wallet_statement = DB::table(DB::raw("(
        -- 💰 Wallet Ledger (Credit)
        SELECT 
            id,
            created_at,
            amount,
            'credit' as type,
            invoice_no,
            'Sale (GST)' as particular,
            created_at as pay_date,
            pay_mode,
            invoice_no as wallet_no,
            remarks
        FROM wallet_ledger
        WHERE customer_id = $id
        AND pay_mode NOT LIKE '%Interest%'

        UNION ALL

        -- 🪙 Interest Ledger
        SELECT 
            id,
            created_at,
            amount,
            'Interest' as type,
            invoice_no,
            'Interest Charge' as particular,
            pay_date,
            pay_mode,
            invoice_no as wallet_no,
            remarks
        FROM wallet_ledger
        WHERE customer_id = $id
        AND pay_mode LIKE '%Interest%'

        UNION ALL

        -- 💸 Orders Paid via Wallet (Debit)
        SELECT 
            id,
            created_at,
            total_amount as amount,
            'debit' as type,
            invoice_no,
            'Payment' as particular,
            created_at as pay_date,
            pay_mode,
            NULL as wallet_no,
            'Order Generated' as remarks
        FROM orders
        WHERE customer_id = $id 
        AND pay_mode = 'wallet'

        UNION ALL 

        -- 💳 Online Payment Credit
        SELECT
            id,
            created_at,
            total_amount as amount,
            'credit' as type,
            invoice_no,
            'Online Payment' as particular,
            created_at as pay_date,
            pay_mode,
            NULL as wallet_no,
            'Payment received successfully' as remarks
            FROM order_estimate
            WHERE customer_id = $id
            AND pay_mode != 'wallet'
            AND payment_status = 'success'

            UNION ALL

            -- 💳 Online Payment Debit (so balance is correct)
            SELECT
                id,
                created_at,
                total_amount as amount,
                'debit' as type,
                invoice_no,
                'Online Payment Deducted' as particular,
                created_at as pay_date,
                pay_mode,
                NULL as wallet_no,
                'Order Payment Deducted' as remarks
            FROM order_estimate
            WHERE customer_id = $id
            AND pay_mode != 'wallet'
            AND payment_status = 'success'

        ) as wallet_union"))
            ->orderBy('created_at', 'asc')
            ->get();

        $balance = 0;

        foreach ($wallet_statement as $entry) {
            if ($entry->type === 'credit' || $entry->type === 'Interest') {
                $balance += $entry->amount;
            } elseif ($entry->type === 'debit') {
                $balance -= $entry->amount;
            }
            $entry->balance = $balance;
        }
        $wallet_statement = $wallet_statement->reverse()->values();

        $extra_charge = DB::table(DB::raw("(
        SELECT id, created_at, amount, remarks
        FROM extracharge
        WHERE customer_id = $id
        ) as ec"))
            ->orderBy('created_at', 'asc')
            ->get();
        return view("suppliers.customer-profile", compact("data", "user", "documents", "wallet_statement", "extra_charge"));
    }

    public function UpdateCompanyDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'company_number' => 'required',
            'customer_type' => 'required',
            'type' => 'required',
            'id' => 'required',

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

            DB::table('customers')->where("id", $request->id)->update(array(

                "type" => $request->type,
                "customer_type" => $request->customer_type,
                "name" => $request->company_name,
                "number" => $request->company_number,
                "email" => $request->company_email,
                "gst" => $request->company_gst,
                "address" => $request->company_address,
                "state" => $request->company_state,
                "city" => $request->company_city,
                "district" => $request->company_district,
                "pincode" => $request->company_pincode,
                "active" => $request->active,
                "supplier_id" => $request->user['supplier_id'],

            ));
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }


    public function UpdatePersonalDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'number' => 'required',
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

            DB::table('customer_users')->where("id", $request->id)->update(array(

                "name" => $request->name,
                "number" => $request->number,
                "email" => $request->email,
                "address" => $request->address,
                "state" => $request->state,
                "city" => $request->city,
                "district" => $request->district,
                "pincode" => $request->pincode,

            ));
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function UploadDocument(Request $request)
    {



        $file = "";
        if ($request->hasFile('file')) {
            $file = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move('documents', $file);
        } else {
            if ($request->id) {
                $product_category =  DB::table("customer_document")->where("id", $request->id)->first();
                $file = $product_category->file;
            }
        }

        try {

            DB::table('customer_document')->where("id", $request->id)->update(array(

                "file" => $file,
                "remarks" => $request->remarks,


            ));
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function UploadAgreement(Request $request)
    {
        $file = "";
        if ($request->hasFile('file')) {
            $file = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move('documents', $file);
        } else {
            if ($request->id) {
                $product_category =  DB::table("customer_document")->where("id", $request->id)->first();
                $file = $product_category->file;
            }
        }

        try {

            DB::table('customers')->where("id", $request->id)->update(array(

                "agreement" => $file,
                "agreement_remarks" => $request->agreement_remarks,
                "active" => 1,


            ));
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }


    public function UploadWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'wallet' => 'required',
            'due_date' => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return redirect()->back()->with('error', $messages[0]);
        }
        try {
            $grace_days = 0;
            switch ($request->due_date) {
                case 1:
                    $grace_days = 0;
                    break;
                case 7:
                    $grace_days = 2;
                    break;
                case 15:
                    $grace_days = 5;
                    break;
                case 30:
                    $grace_days = 10;
                    break;
            }
            $today = Carbon::now();
            $dueDate = $today->copy()->addDays($grace_days);
            $graceEndDate = now()->addDays($request->due_date + $grace_days);
            DB::table('customers')->where('id', $request->id)->update([
                'wallet' => $request->wallet,
                "due_date" => $request->due_date,
                'grace_days' => $grace_days,
                'wallet_assigned_at' => now(),
                'updated_at' => now(),
            ]);
            $invoice_no = 'VOU-' . $request->id . date('YmdHis');
            DB::table('wallet_ledger')->insert(array(
                'customer_id' => $request->id,
                'amount' => $request->wallet,
                'pay_mode' => 'Credit_Limit',
                'pay_date' => now(),
                'supplier_id' => $request->user['supplier_id'],
                'invoice_no' => $invoice_no,
                'remarks' => "Due: {$request->due_date} days | Grace: {$grace_days} days | 18% interest after grace period",
            ));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        return redirect()->back()->with("success", "Wallet updated successfully with grace period and interest rules applied.");
    }


    public function GetProductPrices(Request $request)
    {

        return DB::table("product_price")->where("product_id", $request->id)->get();
    }

    public function DeleteProductPrice(Request $request)
    {
        try {
            DB::table("product_price")->where("id", $request->id)->delete();
            return response()->json(["msg" => "Save successfully", "error" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["msg" => $th->getMessage(), "error" => "error"]);
        }
    }

    public function AddProductPrice(Request $request)
    {
        try {
            DB::table("product_price")->insert(array(
                "product_id" => $request->product_id,
                "price" => $request->product_price,
                "qty" => $request->product_qty,
            ));
            return response()->json(["msg" => "Save successfully", "error" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["msg" => $th->getMessage(), "error" => "error"]);
        }
    }

    public function OrdersEstimate(Request $request, $status)
    {
        $data = DB::table("orders_supplier as a")
            ->select(
                "b.*",
                "a.subtotal",
                "a.shipping_status as status",
                "b.id",
                "a.id as supplier_order_id",
                "c.total_amount" // 👈 total from orders
            )
            ->join("order_estimate as b", "a.order_id", "b.id")
            ->leftJoin("orders as c", "b.id", "c.estimate_id") // 👈 join orders table
            ->where("a.supplier_id", $request->user['supplier_id'])
            ->where("b.order_status", $status)
            ->orderBy("a.id", "desc")
            ->get();

        $suppliers = DB::table("supplier_users")
            ->whereIn("id", $request->userIds)
            ->get();

        return view("suppliers.orders-estimate", compact("data", "suppliers", "status"));
    }

    public function OrderEstimateEdit(Request $request, $id)
    {



        $orders = DB::table("order_estimate as a")
            ->select(
                "a.*",
                "c.name as supplier_name",
                "c.number as supplier_number",
                "c.email as supplier_email",
                "c.address as supplier_address",
                "c.state as supplier_state",
                "c.district as supplier_district",
                "c.city as supplier_city",
                "c.pincode as supplier_pincode",
                "b.subtotal",
                "b.shipping_status as status",
                "b.id as supplier_id",
                "cu.name as customer_name",
                "cu.email as customer_email",
                "cu.number as customer_number",
                "cu.address as customer_address",
                "cu.state as customer_state",
                "cu.district as customer_district",
                "cu.city as customer_city",
                "cu.pincode as customer_pincode",
                "cus.supplier_id"
            )
            ->leftJoin("orders_supplier as b", "a.id", "b.order_id")
            ->leftJoin("suppliers as c", "b.supplier_id", "c.id")
            ->leftJoin("customer_users as cu", "a.customer_id", "cu.id")
            ->leftJoin("customers as cus", "a.customer_id", "cus.id")
            ->where("a.id", $id)
            ->first();
        $det = DB::table("order_estimate_item as a")
            ->select(
                "a.*",
                "b.hsn_code",
                "b.base_price",
                "c.name as uom"
            )
            ->join("products as b", "a.product_id", "b.id")
            ->join("product_uom as c", "b.uom_id", "c.id")
            ->where("a.supplier_id", $orders->supplier_id)
            ->where("a.order_id", $orders->id)
            ->get();
        return view("suppliers.order-estimate-edit", compact("orders", "det"));
    }

    public function getProducts(Request $request)
    {
        $query = DB::table("products")->where("active", 1);

        if ($request->has('search') && !empty($request->search)) {
            $query->where("name", "like", "%" . $request->search . "%");
        }

        $page = $request->input("page", 1);
        $perPage = 200;
        $offset = ($page - 1) * $perPage;
        $totalCount = (clone $query)->count();

        $products = $query
            ->select("id", "name", "base_price")
            ->offset($offset)
            ->limit($perPage)
            ->get();

        foreach ($products as $product) {
            $product->details = DB::table("product_price")
                ->where("product_id", $product->id)
                ->orderBy("qty", "asc")
                ->get();
        }

        return response()->json([
            "results" => $products,
            "pagination" => [
                "more" => ($offset + $perPage) < $totalCount
            ]
        ]);
    }

    public function createOrderEstimate(Request $request)
    {
        $customers = DB::table("customers")
            ->join('customer_users', 'customers.id', '=', 'customer_users.customer_id')
            ->where("customers.active", 1)
            ->select(
                'customers.id as customer_id',
                'customer_users.name',
                'customer_users.number',
                'customer_users.id'
            )
            ->get();
        $data = compact('customers');
        return view('suppliers.create-order-estimate')->with($data);
    }



    public function OrdersSave(Request $request)
    {
        $orderId = $request->estimate_id;
        $paymode = $request->pay_mode;
        $is_invoice = 1;
        $payment_status = "complete";
        $totalAmount = 0;
        $oldholdAmnt = $request->total_amount;
        foreach ($request->price as $i => $price) {
            $qty = $request->qty[$i] ?? 0;
            $gst = $request->gst[$i] ?? 0;
            $cess = $request->cess_tax[$i] ?? 0;

            $itemTotal = $price * $qty;
            $taxTotal = ($itemTotal * ($gst + $cess)) / 100;

            $totalAmount += $itemTotal + $taxTotal;
        }
        $customer = DB::table('customers')->where('id', $request->customer_id)->first();
        $interestAmount = 0;
        if ($paymode == 'wallet' && $customer) {
            $holdAmount = $customer->hold_amount ?? 0;
            $usedWallet = $customer->used_wallet ?? 0;
            if ($usedWallet < 0) {
                $interestAmount = $totalAmount + $usedWallet;
            } else {
                $interestAmount = $totalAmount;
            }
            $newHoldAmount = max($holdAmount - $oldholdAmnt, 0);
            $newUsedWallet = $usedWallet + $totalAmount;
            DB::table('customers')->where('id', $request->customer_id)->update([
                'hold_amount' => $newHoldAmount,
                'used_wallet' => $newUsedWallet,
                'updated_at' => now(),
            ]);
        }
        $order = DB::table('orders')
            ->where('estimate_id', $orderId)
            ->first();
        if ($order) {
            DB::table('orders')->where('id', $order->id)->update([
                'customer_id' => $request->customer_id,
                'total_amount' => $totalAmount,
                'intrest_amount' => $interestAmount,
                'is_invoice' => $is_invoice,
                'invoice_no' => $request->invoice_no,
                'name' => $request->name ?? '',
                'number' => $request->number ?? '',
                'email' => $request->email ?? '',
                'pay_mode' => $paymode,
                'payment_status' => $payment_status,
                'address' => $request->address ?? '',
                'state' => $request->state ?? '',
                'district' => $request->district ?? '',
                'city' => $request->city ?? '',
                'pincode' => $request->pincode ?? '',
                'updated_at' => now(),
            ]);
            $order_id = $order->id;
            DB::table('orders_item')->where('order_id', $order_id)->delete();
        } else {
            $order_id = DB::table('orders')->insertGetId([
                'estimate_id' => $orderId,
                'customer_id' => $request->customer_id,
                'total_amount' => $totalAmount,
                'intrest_amount' => $interestAmount,
                'is_invoice' => $is_invoice,
                'invoice_no' => $request->invoice_no,
                'name' => $request->name ?? '',
                'number' => $request->number ?? '',
                'email' => $request->email ?? '',
                'pay_mode' => $paymode,
                'payment_status' => $payment_status,
                'address' => $request->address ?? '',
                'state' => $request->state ?? '',
                'district' => $request->district ?? '',
                'city' => $request->city ?? '',
                'pincode' => $request->pincode ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        foreach ($request->product_id as $i => $productId) {

            if (!$productId) continue;

            DB::table('orders_item')->insert([
                'supplier_id' => $request->supplier_id,
                'order_id' => $order_id,
                'product_id' => $productId,
                'qty' => $request->qty[$i] ?? 0,
                'price' => $request->price[$i] ?? 0,
                'name' => $request->product_name[$i] ?? '',
                'description' => $request->description[$i] ?? '',
                'gst' => $request->gst[$i] ?? 0,
                'cess_tax' => $request->cess_tax[$i] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('order_estimate')->where('id', $orderId)->update([
            'order_status' => 'complete',
            'updated_at' => now(),
        ]);
        $orderEstimate = DB::table('order_estimate')->where('id', $orderId)->first();
        $customerUser = DB::table('customer_users')->where('customer_id', $orderEstimate->customer_id)->first();
        $orderType = "Order Estimate";
        if ($customerUser && $customerUser->email) {
            try {
                $supplier = DB::table('suppliers')->where('id', $customer->supplier_id)->first();
                $emailTemplate = null;

                if ($supplier && $supplier->email_temp_id) {
                    $emailTemplate = DB::table('email_template')->where('id', $supplier->email_temp_id)->first();
                }
                Mail::to($customerUser->email)->send(
                    new OrderEstimateStatusMail($orderEstimate, $orderType, $emailTemplate)
                );
            } catch (\Throwable $th) {
                Log::error('Failed to send order status email: ' . $th->getMessage());
            }
        }
        try {
            $orderStatus = "Order Confirm";
            $phone = $customerUser->number ?? $customer->number ?? null;
            if (!$phone) {
                Log::warning("⚠️ No phone number for order_estimate_id={$orderEstimate->id}");
                return back()->with('warning', 'No phone number available');
            }
            $cleanPhone = preg_replace('/\D+/', '', $phone);
            if (strlen($cleanPhone) == 10) $cleanPhone = '91' . $cleanPhone;
            $smsConfig = config('services.smswala');
            $message = "Dear Customer, your Order  status has been updated. "
                . "Order ID: #{$orderEstimate->id}, Status: {$orderStatus}, "
                . "Total Amount: ₹{$orderEstimate->total_amount} - Bulk Basket India";
            $msgVars = urlencode("#VAR1#={$orderEstimate->id}&#VAR2#={$orderStatus}&#VAR3#={$orderEstimate->total_amount}");
            $url = "{$smsConfig['url']}?"
                . "key={$smsConfig['key']}"
                . "&campaign={$smsConfig['campaign']}"
                . "&routeid={$smsConfig['routeid']}"
                . "&type=text"
                . "&contacts={$cleanPhone}"
                . "&senderid={$smsConfig['sender']}"
                . "&msg=" . urlencode($message)
                . "&template_id={$smsConfig['templates']['template']}"
                . "&pe_id={$smsConfig['pe_id']}";
            $response = Http::get($url);
            if ($response->successful() && (stripos($response->body(), 'SMS-SHOOT-ID') !== false || stripos($response->body(), 'SUCCESS') !== false)) {
                Log::info("✅ SMS sent successfully for order_estimate_id={$orderEstimate->id}, to={$cleanPhone}, resp=" . $response->body());
            } else {
                Log::error("❌ SMS failed for order_estimate_id={$orderEstimate->id}, status=" . $response->status() . ", resp=" . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error("❌ SMS Exception: " . $e->getMessage());
        }

        return redirect("supplier/orders-estimate/complete")->with('success', 'Order saved successfully.');
    }


    public function getProductDetails($id)
    {
        $product = DB::table("products")
            ->select("id", "name", "base_price")
            ->where("id", $id)
            ->first();

        $prices = DB::table("product_price")
            ->where("product_id", $id)
            ->orderBy("qty", "asc")
            ->get();

        return response()->json([
            "status" => true,
            "product" => $product,
            "prices" => $prices
        ]);
    }


    public function OrderDetailsEstimate(Request $request, $id)
    {
        $orders = DB::table("order_estimate as a")
            ->select(
                "a.*",
                "c.name as supplier_name",
                "c.number as supplier_number",
                "c.email as supplier_email",
                "c.address as supplier_address",
                "c.state as supplier_state",
                "c.district as supplier_district",
                "c.city as supplier_city",
                "c.pincode as supplier_pincode",
                "b.subtotal",
                "b.shipping_status as status",
                "b.id as supplier_id",
                "cu.name as customer_name",
                "cu.email as customer_email",
                "cu.number as customer_number",
                "cu.address as customer_address",
                "cu.state as customer_state",
                "cu.district as customer_district",
                "cu.city as customer_city",
                "cu.pincode as customer_pincode"
            )
            ->leftJoin("orders_supplier as b", "a.id", "b.order_id")
            ->leftJoin("suppliers as c", "b.supplier_id", "c.id")
            ->leftJoin("customer_users as cu", "a.customer_id", "cu.id")
            ->leftJoin("customers as cus", "a.customer_id", "cus.id")
            ->where("a.id", $id)
            ->first();

        $det = DB::table("order_estimate_item as a")
            ->select(
                "a.*",
                "b.hsn_code",
                "b.gst",
                "b.description",
                "b.cess_tax",
                "c.name as uom"
            )
            ->join("products as b", "a.product_id", "b.id")
            ->join("product_uom as c", "b.uom_id", "c.id")
            // ->where("a.supplier_id", $orders->supplier_id)
            ->where("a.order_id", $orders->id)
            ->get();
        return view("suppliers.order-estimate-details", compact("orders", "det"));
    }

    public function OrderEstimateRequestPrice(Request $request, $id)
    {
        $orders = DB::table("order_estimate as a")
            ->select(
                "a.*",
                "cu.name as customer_name",
                "cu.email as customer_email",
                "cu.number as customer_number",
                "cu.address as customer_address",
                "cu.state as customer_state",
                "cu.district as customer_district",
                "cu.city as customer_city",
                "cu.pincode as customer_pincode"
            )
            ->join("customer_users as cu", "a.customer_id", "cu.id")
            ->where("a.id", $id)
            ->first();

        $requests = DB::table("request_for_price_mst")
            ->where("order_estimate_id", $id)
            ->orderBy("id", "desc")
            ->get();

        foreach ($requests as $req) {
            $req->items = DB::table("request_for_price_det as a")
                ->select(
                    "a.*",
                    "b.name as product_name",
                    "b.image as product_image",
                    "b.hsn_code",
                    "c.name as uom"
                )
                ->join("products as b", "a.product_id", "b.id")
                ->leftJoin("product_uom as c", "b.uom_id", "c.id")
                ->where("a.request_mst_id", $req->id)
                ->get();
        }

        return view("suppliers.order-estimate-request-price", compact("orders", "requests"));
    }


    // public function Orders(Request $request, $status)
    // {
    //     $data =  DB::table("orders_supplier as a")
    //         ->select("b.*", "a.subtotal", "a.shipping_status as status", "b.id", "a.id as supplier_order_id")
    //         ->join("orders as b", "a.order_id", "b.id")
    //         ->where("a.supplier_id", $request->user['supplier_id'])
    //         ->where("a.shipping_status", $status)
    //         ->orderBy("a.id", "desc")->get();
    //     $suppliers = DB::table("supplier_users")->whereIn("id", $request->userIds)->get();
    //     return view("suppliers.orders", compact("data", "suppliers"));
    // }
    public function Orders(Request $request, $status)
    {
        $status = request("status");
        $data =  DB::table("orders_supplier as a")
            ->select("b.*", "a.subtotal", "a.shipping_status as status", "b.id", "a.id as supplier_order_id")
            ->join("orders as b", "a.order_id", "b.id")
            ->where("a.supplier_id", $request->user['supplier_id']);
        if ($status) {
            $data->where("a.status", $status);
        }
        $orders =  $data->orderBy("id", "desc")
            ->get();
        $suppliers = DB::table("supplier_users")->whereIn("id", $request->userIds)->get();
        return view("suppliers.orders", compact("data", "suppliers"));
    }

    // public function OrdersManagement(Request $request )
    // {
    //     $query = DB::table("orders_supplier as a")
    //         ->select(
    //             "b.*",
    //             "a.subtotal",
    //             "a.shipping_status as status",
    //             "b.id as order_id",
    //             "a.id as supplier_order_id",
    //             "c.name as customer_name",
    //             "c.email as customer_email",
    //             "c.number as customer_phone"
    //         )
    //         ->join("orders as b", "a.order_id", "b.id")
    //         ->join("customers as c", "b.customer_id", "c.id")
    //         ->where("a.supplier_id", $request->user['supplier_id'])
    //         ->orderBy("a.id", "desc");

    //     if ($request->has('status') && $request->status !== 'all') {
    //         $query->where("b.order_status", $request->status);
    //     }
    //     $data = $query->get();
    //     $suppliers = DB::table("supplier_users")
    //         ->whereIn("id", $request->userIds)
    //         ->get();
    //     $status = $request->status ?? 'all';
    //     return view("suppliers.orders-management", compact("data", "suppliers", "status"));
    // }

    public function OrdersManagement(Request $request)
    {
        $status = $request->status ?? 'pending';

        $query = DB::table("orders_supplier as a")
            ->select(
                "b.*",
                "a.subtotal",
                "a.shipping_status as shipping_status",
                "b.status as order_status",
                "b.id as order_id",
                "a.id as supplier_order_id",
                "c.name as customer_name",
                "c.email as customer_email",
                "c.number as customer_phone"
            )
            ->join("orders as b", "a.order_id", "=", "b.id")
            ->join("customers as c", "b.customer_id", "=", "c.id")
            ->where("a.supplier_id", $request->user['supplier_id']);
        if ($status != 'all') {
            $query->whereRaw("LOWER(b.status) = ?", [strtolower($status)]);
        }
        $data = $query->orderBy("a.id", "desc")->get();
        $suppliers = DB::table("supplier_users")
            ->whereIn("id", $request->userIds)
            ->get();
        return view("suppliers.orders-management", compact("data", "suppliers", "status"));
    }




    public function OrderDetails(Request $request, $id)
    {
        $orders = DB::table("orders as a")
            ->select(
                "a.*",
                "c.name as supplier_name",
                "c.number as supplier_number",
                "c.email as supplier_email",
                "c.address as supplier_address",
                "c.state as supplier_state",
                "c.district as supplier_district",
                "c.city as supplier_city",
                "c.pincode as supplier_pincode",
                "b.subtotal",
                "b.shipping_status as status",
                "b.id as supplier_id",
                "cu.name as customer_name",
                "cu.email as customer_email",
                "cu.number as customer_number",
                "cu.address as customer_address",
                "cu.state as customer_state",
                "cu.district as customer_district",
                "cu.city as customer_city",
                "cu.pincode as customer_pincode"
            )
            ->leftJoin("orders_supplier as b", "a.id", "b.order_id")
            ->leftJoin("suppliers as c", "b.supplier_id", "c.id")
            ->leftJoin("customer_users as cu", "a.customer_id", "cu.id")
            ->where("a.estimate_id", $id)
            ->first();
        $det = DB::table("orders_item as a")
            ->select("a.*", "b.article_no", "c.name as uom")
            ->join("products as b", "a.product_id", "b.id")
            ->join("product_uom as c", "b.uom_id", "c.id")
            // ->where("a.supplier_id", $orders->supplier_id)
            // ->where("a.order_id", $orders->estimate_id)
            ->where("a.order_id", $orders->id)
            ->get();

        return view("suppliers.order-details", compact("orders", "det"));
    }

    public function UpdateOrderStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',

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

            DB::table('orders_supplier')->where("id", $request->id)->update(array(
                "shipping_status" => $request->status,
                "user_id" => $request->user_id,
            ));
            DB::table('orders')->where("id", $request->id)->update(array(
                "order_status" => $request->status,
                "user_id" => $request->user_id,
            ));
            $order = DB::table('orders')->where('id', $request->id)->first();
            $suppliers = DB::table("supplier_users")->whereIn("id", $request->userIds)->first();
            $customer = DB::table('customers')->where('id', $order->customer_id)->first();
            $customerUser = DB::table('customer_users')->where('customer_id', $order->customer_id)->first();
            $orderType = "Order";
            if ($customerUser && $customerUser->email) {
                try {
                    $supplier = DB::table('suppliers')->where('id', $customer->supplier_id)->first();
                    $emailTemplate = null;

                    if ($supplier && $supplier->email_temp_id) {
                        $emailTemplate = DB::table('email_template')->where('id', $supplier->email_temp_id)->first();
                    }
                    Mail::to($customerUser->email)->send(
                        new OrderEstimateStatusMail($order, $orderType, $emailTemplate, $suppliers)
                    );
                } catch (\Throwable $th) {
                    Log::error('Failed to send order status email: ' . $th->getMessage());
                }
            }
            $phone = $customerUser->number ?? $customer->number ?? null;
            if (!$phone) {
                Log::warning("⚠️ No phone number for order_estimate_id={$order->id}");
                return back()->with('warning', 'No phone number available');
            }
            $cleanPhone = preg_replace('/\D+/', '', $phone);
            if (strlen($cleanPhone) == 10) $cleanPhone = '91' . $cleanPhone;
            $smsConfig = config('services.smswala');
            $message = "Dear Customer, your Order  status has been updated. "
                . "Order ID: #{$order->id}, Status: {$order->order_status}, "
                . "Total Amount: ₹{$order->total_amount} - Bulk Basket India";
            $msgVars = urlencode("#VAR1#={$order->id}&#VAR2#={$order->order_status}&#VAR3#={$order->total_amount}");
            $url = "{$smsConfig['url']}?"
                . "key={$smsConfig['key']}"
                . "&campaign={$smsConfig['campaign']}"
                . "&routeid={$smsConfig['routeid']}"
                . "&type=text"
                . "&contacts={$cleanPhone}"
                . "&senderid={$smsConfig['sender']}"
                . "&msg=" . urlencode($message)
                . "&template_id={$smsConfig['templates']['template']}"
                . "&pe_id={$smsConfig['pe_id']}";
            $response = Http::get($url);
            if ($response->successful() && (stripos($response->body(), 'SMS-SHOOT-ID') !== false || stripos($response->body(), 'SUCCESS') !== false)) {
                Log::info("✅ SMS sent successfully for order_estimate_id={$order->id}, to={$cleanPhone}, resp=" . $response->body());
            } else {
                Log::error("❌ SMS failed for order_estimate_id={$order->id}, status=" . $response->status() . ", resp=" . $response->body());
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function AddWalletLedger(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'amount' => 'required',
            'pay_mode' => 'required',
            'pay_date' => 'required',
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
            $invoice_no = 'VOU-' . $request->customer_id . date('YmdHis');
            DB::table('wallet_ledger')->insert(array(
                'customer_id' => $request->customer_id,
                'amount' => $request->amount,
                'pay_mode' => $request->pay_mode,
                'pay_date' => $request->pay_date,
                'supplier_id' => $request->user['supplier_id'],
                'invoice_no' => $invoice_no,
                "remarks" => $request->remarks
            ));
            DB::table("customers")->where("id", $request->customer_id)->decrement("used_wallet", $request->amount);

            $ledgerAmount = $request->amount;
            if ($ledgerAmount > 0) {
                $orders = DB::table('orders')
                    ->where('customer_id', $request->customer_id)
                    ->where('intrest_amount', '>', 0)
                    ->orderBy('id', 'asc')
                    ->get();
                foreach ($orders as $order) {
                    $currentInterest = $order->intrest_amount;
                    if ($currentInterest > 0) {
                        if ($ledgerAmount >= $currentInterest) {
                            DB::table('orders')->where('id', $order->id)->update([
                                'intrest_amount' => 0,
                            ]);
                            $ledgerAmount -= $currentInterest;
                        } else {
                            $newInterest = $currentInterest - $ledgerAmount;
                            DB::table('orders')->where('id', $order->id)->update([
                                'intrest_amount' => $newInterest,
                            ]);
                            $ledgerAmount = 0;
                            break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function AddExtraCharge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'amount' => 'required',
            'remarks' => 'required',
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
            DB::table('extracharge')->insert(array(
                'customer_id' => $request->customer_id,
                'amount' => $request->amount,
                'supplier_id' => $request->user['supplier_id'],
                "remarks" => $request->remarks
            ));
            $orders = DB::table("orders")->where("customer_id", $request->customer_id)->first();
            if ($orders) {
                DB::table("customers")->where("id", $request->customer_id)->decrement("used_wallet", $request->amount);
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function WalletManagement(Request $request)
    {
        $data =  DB::table("customers")->where("supplier_id", $request->user['supplier_id'])->get();
        return view("suppliers.wallet-management", compact("data"));
    }

    public function UserRole(Request $request)
    {
        $data = SupplierRole::where("supplier_id", $request->user['supplier_id'])->get();
        return view("suppliers.user-role", compact("data"));
    }

    public function saveUserRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'app_permission' => 'required',
        ]);


        if ($validator->fails()) {

            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {

            if ($request->id) {
                $supplierRole =  SupplierRole::findOrFail($request->id);
                $supplierRole->name = $request->name;
                $supplierRole->app_permission = $request->app_permission;
                $supplierRole->save();
            } else {
                $supplierRole = new SupplierRole();
                $supplierRole->name = $request->name;
                $supplierRole->app_permission = $request->app_permission;
                $supplierRole->supplier_id = $request->user["supplier_id"];
                $supplierRole->save();
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function users(Request $request)
    {
        $data   = SupplierUsers::where("supplier_id", $request->user["supplier_id"])->where("parent_id", '!=', 0)->get();
        $supplierRole = SupplierRole::where("supplier_id", $request->user['supplier_id'])->get();
        $parents = SupplierUsers::where("supplier_id", $request->user["supplier_id"])->get();
        return view("suppliers.users", compact("data", "supplierRole", "parents"));
    }

    public function updateSupplierUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'number' => 'required',
            'password' => 'required',
            'role_id' => 'required',
            'parent_id' => 'required',
        ]);


        if ($validator->fails()) {

            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            if ($request->id) {
                $supplierRole =  SupplierUsers::findOrFail($request->id);
                $supplierRole->name = $request->name;
                $supplierRole->number = $request->number;
                $supplierRole->email = $request->email;
                $supplierRole->address = $request->address;
                $supplierRole->state = $request->state;
                $supplierRole->city = $request->city;
                $supplierRole->district = $request->district;
                $supplierRole->pincode = $request->pincode;
                $supplierRole->password = $request->password;
                $supplierRole->parent_id = $request->parent_id;
                $supplierRole->role_id = $request->role_id;
                $supplierRole->save();
            } else {
                $supplierRole =  new SupplierUsers();
                $supplierRole->name = $request->name;
                $supplierRole->number = $request->number;
                $supplierRole->email = $request->email;
                $supplierRole->address = $request->address;
                $supplierRole->state = $request->state;
                $supplierRole->city = $request->city;
                $supplierRole->district = $request->district;
                $supplierRole->pincode = $request->pincode;
                $supplierRole->password = $request->password;
                $supplierRole->parent_id = $request->parent_id;
                $supplierRole->role_id = $request->role_id;
                $supplierRole->supplier_id = $request->user["supplier_id"];
                $supplierRole->save();
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }


    public function logout(Request $request)
    {

        DB::table('supplier_users')->where("token", session("token"))->update(array(
            'token' => "",
        ));
        session()->flush();
        return redirect("/")->with("success", "logout successfully");
    }


    public function getMultipleImages(Request $request)
    {
        return DB::table("product_images")->where("product_id", $request->id)->get();
    }

    public function deleteImage(Request $request)
    {
        DB::table("product_images")->where("id", $request->id)->delete();
    }

    public function getProduct(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table("products as a")
                ->select("a.*", "b.name as brand", "c.name as category", "d.name as sub_category", "e.name as uom")
                ->leftJoin("product_brand as b", "a.brand_id", "b.id")
                ->join("product_category as c", "a.category_id", "c.id")
                ->join("product_sub_category as d", "a.sub_category_id", "d.id")
                ->join("product_uom as e", "a.uom_id", "e.id")
                ->where("a.supplier_id", $request->user['supplier_id']);
            if ($request->filled('search_category')) {
                $query->where("a.category_id", $request->search_category);
            }
            if ($request->filled('sub_category_search')) {
                $query->where("a.sub_category_id", $request->sub_category_search);
            }
            $data = $query->get();

            return DataTables::of($data)
                ->editColumn('image', function ($row) {
                    $src = asset('product images/' . $row->image);
                    $dummy = asset('images/dummy.png');

                    return '<img src="' . $src . '" 
                    onerror="this.onerror=null;this.src=\'' . $dummy . '\';"
                    style="width:80px;height:80px;object-fit:cover;aspect-ratio:1/1;">';
                })

                ->editColumn('active', function ($row) {
                    return $row->active == 1
                        ? '<div class="form-check form-switch">
                            <input class="form-check-input is_active" type="checkbox" value="' . $row->id . '"  role="switch"" checked>
                            </div>'
                        : '<div class="form-check form-switch">
                            <input class="form-check-input is_active" type="checkbox" role="switch" value="' . $row->id . '">
                        
                            </div>';
                })
                ->editColumn('is_deal', function ($row) {
                    return $row->is_deal == 1
                        ? '<div class="form-check  form-switch">
                            <input class="form-check-input is_deal" type="checkbox" value="' . $row->id . '"  role="switch"" checked>
                            </div>'
                        : '<div class="form-check  form-switch">
                            <input class="form-check-input is_deal" type="checkbox" role="switch"  value="' . $row->id . '" >
                        
                            </div>';
                })
                ->editColumn('is_discount', function ($row) {
                    return $row->is_discount == 1
                        ? '<div class="form-check  form-switch">
                            <input class="form-check-input is_discount" type="checkbox" value="' . $row->id . '"  role="switch"" checked>
                            </div>'
                        : '<div class="form-check  form-switch">
                            <input class="form-check-input is_discount" type="checkbox" role="switch"  value="' . $row->id . '" >                        
                            </div>';
                })

                ->addColumn('action', function ($row) {
                    $jsonData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                    return '
                <button class="btn btn-primary btn-sm edit" data-data="' . $jsonData . '" type="button"
                        data-category="' . $row->category . '" data-sub_category="' . $row->sub_category . '">
                    <i class="fa fa-pencil" aria-hidden="true"></i>
                </button>
                <button class="btn btn-secondary btn-sm products" type="button" value="' . $row->id . '">
                    <i class="fa fa-eye" aria-hidden="true"></i>
                </button>
                <button class="btn btn-dark btn-sm uploadImages" type="button" value="' . $row->id . '">
                    Upload Images
                </button>
               <button class="btn btn-info btn-sm wareHouseAllocation"
                        data-id="' . $row->id . '">
                    Ware House Allocation
                </button>
            ';
                })
                ->rawColumns(['image', 'active', "is_deal", "is_discount", 'action'])
                ->make(true);
        }
    }


    public function EditEstimateOrder(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'order_status' => 'required',
        ]);
        DB::table('order_estimate')
            ->where('id', $request->id)
            ->update(['order_status' => $request->order_status]);
        $orderEstimate = DB::table('order_estimate')->where('id', $request->id)->first();
        if ($orderEstimate) {
            $customer = DB::table('customers')->where('id', $orderEstimate->customer_id)->first();
            if (strtolower($request->order_status) === 'cancel' && $customer) {
                $newHoldAmount = max(0, $customer->hold_amount - $orderEstimate->total_amount);
                DB::table('customers')
                    ->where('id', $orderEstimate->customer_id)
                    ->update([
                        'hold_amount' => $newHoldAmount,
                        'updated_at' => now(),
                    ]);
            }
            $customerUser = DB::table('customer_users')->where('customer_id', $orderEstimate->customer_id)->first();
            $orderType = "Order Estimate";
            if ($customerUser && $customerUser->email) {
                try {
                    $supplier = DB::table('suppliers')->where('id', $customer->supplier_id)->first();
                    $emailTemplate = $supplier && $supplier->email_temp_id
                        ? DB::table('email_template')->where('id', $supplier->email_temp_id)->first()
                        : null;
                    Mail::to($customerUser->email)->send(
                        new OrderEstimateStatusMail($orderEstimate, $orderType, $emailTemplate)
                    );
                } catch (\Throwable $th) {
                    Log::error('Failed to send order status email: ' . $th->getMessage());
                }
            }
            try {
                $phone = $customerUser->number ?? $customer->number ?? null;
                if (!$phone) {
                    Log::warning("⚠️ No phone number for order_estimate_id={$orderEstimate->id}");
                    return back()->with('warning', 'No phone number available');
                }
                $cleanPhone = preg_replace('/\D+/', '', $phone);
                if (strlen($cleanPhone) == 10) $cleanPhone = '91' . $cleanPhone;
                $smsConfig = config('services.smswala');
                $message = "Dear Customer, your Order  status has been updated. "
                    . "Order ID: #{$orderEstimate->id}, Status: {$request->order_status}, "
                    . "Total Amount: ₹{$orderEstimate->total_amount} - Bulk Basket India";
                $msgVars = urlencode("#VAR1#={$orderEstimate->id}&#VAR2#={$request->order_status}&#VAR3#={$orderEstimate->total_amount}");
                $url = "{$smsConfig['url']}?"
                    . "key={$smsConfig['key']}"
                    . "&campaign={$smsConfig['campaign']}"
                    . "&routeid={$smsConfig['routeid']}"
                    . "&type=text"
                    . "&contacts={$cleanPhone}"
                    . "&senderid={$smsConfig['sender']}"
                    . "&msg=" . urlencode($message)
                    . "&template_id={$smsConfig['templates']['template']}"
                    . "&pe_id={$smsConfig['pe_id']}";
                $response = Http::get($url);
                if ($response->successful() && (stripos($response->body(), 'SMS-SHOOT-ID') !== false || stripos($response->body(), 'SUCCESS') !== false)) {
                    Log::info("✅ SMS sent successfully for order_estimate_id={$orderEstimate->id}, to={$cleanPhone}, resp=" . $response->body());
                } else {
                    Log::error("❌ SMS failed for order_estimate_id={$orderEstimate->id}, status=" . $response->status() . ", resp=" . $response->body());
                }
            } catch (\Throwable $e) {
                Log::error("❌ SMS Exception: " . $e->getMessage());
            }
            return redirect()->back()->with('success', 'Order status updated, email and SMS sent successfully!');
        }
    }


    public function requestListProduct(Request $request)
    {
        $supplierId = $request->user['supplier_id'];
        $data = DB::table('request_for_product as pfr')
            ->join('customer_users as cu', 'pfr.phone', '=', 'cu.number')
            ->join('customers as c', 'cu.id', '=', 'c.id')
            ->where('c.supplier_id', $supplierId)
            ->select('pfr.*', 'cu.name as customer_name', 'c.supplier_id')
            ->orderBy('pfr.id', 'desc')
            ->get();

        return view('suppliers.product-for-request', compact('data'));
    }

    public function UpdateProductRequestStatus(Request $request)
    {
        DB::table("request_for_product")
            ->where("id", $request->id)
            ->update([
                "status" => $request->status,
            ]);
        return redirect()->back()->with('success', 'Status updated successfully!');
    }

    public function helpSupport(Request $request)
    {
        $customerList = DB::table('help_support as h')
            ->join('customers as c', 'h.customer_id', '=', 'c.id')
            ->select(
                'h.customer_id',
                'c.name as customer_name',
                DB::raw('SUM(CASE WHEN h.is_seen = 0 THEN 1 ELSE 0 END) as unseen_count')
            )
            ->groupBy('h.customer_id', 'c.name')
            ->get();
        return view('suppliers.help-support', compact('customerList'));
    }

    public function markAsSeen(Request $request)
    {
        $customerId = $request->customer_id;
        DB::table('help_support')
            ->where('customer_id', $customerId)
            ->where('is_seen', 0)
            ->update(['is_seen' => 1]);
        return response()->json(['status' => 'success']);
    }

    public function getZonesByWarehouse(Request $request)
    {
        return WareHouseZone::where('is_active', 1)->get();
    }

    public function getLocationByZone(Request $request)
    {
        return WarehouseLocation::where('warehouse_id', $request->warehouse_id)
            ->where('zone_id', $request->zone_id)
            ->where('is_active', 1)
            ->select('id', 'location_code')
            ->get();
    }


    public function saveProductWarehouseAllocation(Request $request)
    {
        WarehouseProductsLocation::updateOrCreate(
            [
                'product_id' => $request->product_id
            ],
            [
                'warehouse_id' => $request->warehouse_id,
                'warehouse_location_id' => $request->location_id,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Allocation Saved'
        ]);
    }

    public function removeAllocation(Request $request)
    {
        WarehouseProductsLocation::where('id', $request->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Allocation Removed'
        ]);
    }

    public function vendorList(Request $request)
    {
        $company_settings = DB::table("suppliers")
            ->where("id", $request->user["supplier_id"])
            ->first();
        $vndcode1 = $company_settings->vendor_prefix;
        $vndcode2 = (int) $company_settings->vendor_no;
        $nextNumber = $vndcode2 + 1;
        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $nextVndr = $vndcode1 . $formattedNumber;
        $data = Vendor::where("supplier_id", $request->user['supplier_id'])->get();
        return view("suppliers.vendor", compact("data", "nextVndr"));
    }

    public function saveVendor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required',
            'dealer_type'  => 'required',
            'address1'    => 'required',
            'district'  => 'required',
            'city'  => 'required',
            'state'  => 'required',
        ]);
        if ($validator->fails()) {

            return redirect()->back()->with('error', $validator->errors()->first());
        }
        try {

            if ($request->id) {
                $vendor =  Vendor::findOrFail($request->id);
                $vendor->company       = $request->company;
                $vendor->dealer_type   = $request->dealer_type;
                $vendor->gst           = $request->gst;
                $vendor->pan_no        = $request->pan_no;
                $vendor->fssai_no      = $request->fssai_no;
                $vendor->address1      = $request->address1;
                $vendor->address2      = $request->address2;
                $vendor->state         = $request->state;
                $vendor->district      = $request->district;
                $vendor->city          = $request->city;
                $vendor->pincode       = $request->pincode;
                $vendor->name          = $request->name;
                $vendor->number        = $request->number;
                $vendor->whatsapp_no   = $request->whatsapp_no;
                $vendor->email         = $request->email;
                $vendor->supplier_id = $request->user["supplier_id"];
                $vendor->save();
            } else {
                $vendor = new Vendor();
                $vendor->vendor_code   = $request->vendor_code;
                $vendor->company       = $request->company;
                $vendor->dealer_type   = $request->dealer_type;
                $vendor->gst           = $request->gst;
                $vendor->pan_no        = $request->pan_no;
                $vendor->fssai_no      = $request->fssai_no;
                $vendor->address1      = $request->address1;
                $vendor->address2      = $request->address2;
                $vendor->state         = $request->state;
                $vendor->district      = $request->district;
                $vendor->city          = $request->city;
                $vendor->pincode       = $request->pincode;
                $vendor->name          = $request->name;
                $vendor->number        = $request->number;
                $vendor->whatsapp_no   = $request->whatsapp_no;
                $vendor->email         = $request->email;
                $vendor->supplier_id = $request->user["supplier_id"];
                $vendor->save();
                $company_settings =  DB::table("suppliers")->where("id", $request->user["supplier_id"])->increment("vendor_no", 1);
                DB::commit();
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function saveVendorAjax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required',
            'dealer_type'  => 'required',
            'address1'    => 'required',
            'district'  => 'required',
            'city'  => 'required',
            'state'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {

            if ($request->id) {
                $vendor = Vendor::findOrFail($request->id);
            } else {
                $vendor = new Vendor();
                $vendor->supplier_id = $request->user["supplier_id"];
            }
            $vendor->vendor_code   = $request->vendor_code;
            $vendor->company       = $request->company;
            $vendor->dealer_type   = $request->dealer_type;
            $vendor->gst           = $request->gst;
            $vendor->pan_no        = $request->pan_no;
            $vendor->fssai_no      = $request->fssai_no;

            $vendor->address1      = $request->address1;
            $vendor->address2      = $request->address2;

            $vendor->state         = $request->state;
            $vendor->district      = $request->district;
            $vendor->city          = $request->city;
            $vendor->pincode       = $request->pincode;

            $vendor->name          = $request->name;
            $vendor->number        = $request->number;
            $vendor->whatsapp_no   = $request->whatsapp_no;
            $vendor->email         = $request->email;
            $vendor->save();
            $company_settings =  DB::table("suppliers")->where("id", $request->user["supplier_id"])->increment("vendor_no", 1);
            DB::commit();
            return response()->json([
                'status'     => true,
                'message'    => 'Vendor saved successfully',
                'vendor_id'  => $vendor->id   // 🔥 Important for dropdown select
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function getVendorsAjax(Request $request)
    {
        $vendors = Vendor::where('active', 1)
            ->where('supplier_id', $request->user["supplier_id"])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $vendors
        ]);
    }

    public function vendorProductList(Request $request, $id)
    {
        $vendorDetail = Vendor::where('id', $id)->where('supplier_id', $request->user["supplier_id"])->first();
        // $products = DB::table('warehouse_product as wp')
        //     ->join('products as p', 'p.id', '=', 'wp.product_id')
        //     ->leftJoin('product_brand as b', 'b.id', '=', 'p.brand_id')
        //     ->leftJoin('product_category as c', 'c.id', '=', 'p.category_id')
        //     ->leftJoin('product_sub_category as s', 's.id', '=', 'p.sub_category_id')
        //     ->where('p.active', 1)
        //     ->select(
        //         'p.id',
        //         'p.name',
        //         'p.hsn_code',
        //         'p.base_price',
        //         'b.name as brand_name',
        //         'c.name as category_name',
        //         's.name as subcategory_name'
        //     )
        //     ->get();
        $products = DB::table('products as p')
            ->leftJoin('product_brand as b', 'b.id', '=', 'p.brand_id')
            ->leftJoin('product_category as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('product_sub_category as s', 's.id', '=', 'p.sub_category_id')
            ->where('p.active', 1)
            ->select(
                'p.id',
                'p.name',
                'p.hsn_code',
                'p.base_price',
                'b.name as brand_name',
                'c.name as category_name',
                's.name as subcategory_name'
            )
            ->get();
        $vendorProducts = DB::table('vendor_products as vp')
            ->join('products as p', 'p.id', '=', 'vp.product_id')
            ->leftJoin('product_brand as b', 'b.id', '=', 'p.brand_id')
            ->leftJoin('product_category as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('product_sub_category as s', 's.id', '=', 'p.sub_category_id')
            ->where('vp.vendor_id', $id)
            ->where('vp.supplier_id', $request->user["supplier_id"])
            ->where('p.active', 1)
            ->select(
                'vp.id as vendor_product_id',
                'p.id',
                'p.name',
                'p.hsn_code',
                'p.base_price',
                'p.active',
                'b.name as brand_name',
                'c.name as category_name',
                's.name as subcategory_name'
            )
            ->get();
        $data = compact('vendorDetail', 'products', 'vendorProducts');
        return view("suppliers.vendor-product-list")->with($data);
    }

    public function AllocateProduct(Request $request)
    {
        $supplier_id = $request->user["supplier_id"];
        $vendor_id = $request->vendor_id;

        $product_ids = $request->product_ids ?? [];

        foreach ($product_ids as $product_id) {

            VendorProducts::updateOrCreate(
                [
                    'vendor_id' => $vendor_id,
                    'product_id' => $product_id
                ],
                [
                    'supplier_id' => $supplier_id
                ]
            );
        }

        return redirect()->back()->with("success", "Save Successfully");
    }

    public function SaveVendorAllocation(Request $request)
    {
        foreach ($request->vendor_id as $vendor) {

            DB::table('vendor_products')->updateOrInsert(
                [
                    'product_id' => $request->product_id,
                    'supplier_id' => $request->user["supplier_id"],
                    'vendor_id' => $vendor
                ],
                [
                    'product_id' => $request->product_id,
                    'supplier_id' => $request->user["supplier_id"],
                    'vendor_id' => $vendor
                ]
            );
        }
        return response()->json([
            'status' => true,
            'message' => 'Vendor Allocation Saved'
        ]);
    }

    public function GetVendorAllocation(Request $request)
    {
        $data = DB::table('vendor_products as vp')
            ->join('products as p', 'p.id', '=', 'vp.product_id')
            ->join('vendor as v', 'v.id', '=', 'vp.vendor_id')
            ->where('vp.product_id', $request->product_id)
            ->where('v.supplier_id', $request->user["supplier_id"])
            ->select(
                'vp.id',
                'p.name as product_name',
                'v.name as vendor_name'
            )
            ->get();

        return response()->json($data);
    }

    public function RemoveVendorAllocation(Request $request)
    {
        DB::table('vendor_products')
            ->where('id', $request->id)
            ->delete();
        return response()->json([
            'status' => true,
            'message' => 'Vendor Removed Successfully'
        ]);
    }
}
