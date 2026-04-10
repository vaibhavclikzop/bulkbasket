<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\WareHouse;
use App\Models\WarehouseLocation;
use App\Models\WareHouseZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Csv\Reader;

use function Laravel\Prompts\table;

class PoController extends Controller
{
    public function GeneratePo(Request $request)
    {
        $vendor = Vendor::where('active', 1)->get();
        $company_settings = DB::table("suppliers")
            ->where("id", $request->user["supplier_id"])
            ->first();
        $prefix = $company_settings->invoice_prefix;
        $currentNo = (int) $company_settings->invoice_no;
        $nextNo = $currentNo + 1;
        $nextPoId = $prefix . $nextNo;
        $vndcode1 = $company_settings->vendor_prefix;
        $vndcode2 = (int) $company_settings->vendor_no;
        $nextNumber = $vndcode2 + 1;
        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $nextVndr = $vndcode1 . $formattedNumber;
        $id = request("id");
        $data = collect();
        $det = collect();
        if ($id) {
            $data = DB::table("po_mst")->where("id", $id)->first();
            $det = DB::table("po_det as a")
                ->select("a.*", "b.name",  "b.hsn_code", "b.article_no")
                ->join("products as b", "a.product_id", "b.id")
                ->where("a.mst_id", $id)->get();
        }
        $gst = DB::table("product_gst")->get();
        $brand = DB::table("product_brand")->get();
        $category = DB::table("product_category")->get();
        $product_uom = DB::table("product_uom")->get();
        return view('suppliers.generate-po', compact("vendor", "gst", "data", "det", "nextPoId", "nextVndr", "brand", "category", "product_uom"));
    }

    // public function GetVendorProducts(Request $request)
    // {
    //     $vendor_id = $request->id;

    //     $products = DB::table('vendor_products as vp')
    //         ->join('products as p', 'p.id', '=', 'vp.product_id')
    //         ->join('warehouse_product as wp', 'wp.product_id', '=', 'p.id')
    //         ->join('warehouse_location as wl', function ($join) {
    //             $join->on('wl.id', '=', 'wp.warehouse_location_id')
    //                 ->on('wl.warehouse_id', '=', 'wp.warehouse_id');
    //         })
    //         ->join('warehouse as w', 'w.id', '=', 'wl.warehouse_id')
    //         ->where('vp.vendor_id', $vendor_id)
    //         ->where('vp.supplier_id', $request->user['supplier_id'])
    //         ->where('p.active', 1)
    //         ->select(
    //             'p.id',
    //             'p.name',
    //             'p.article_no',
    //             'p.base_price',
    //             'p.gst',

    //             'w.id as warehouse_id',          // ✅ ADD
    //             'w.name as warehouse_name',

    //             'wl.id as location_id',          // ✅ ADD
    //             'wl.location_code'
    //         )
    //         ->get();

    //     return response()->json($products);
    // }

    public function GetVendorProducts(Request $request)
    {
        $products = DB::table("products as a")
            ->leftJoin("product_brand as b", "a.brand_id", "b.id")
            ->leftJoin("warehouse_product as wp", "wp.product_id", "a.id")
            ->leftJoin("warehouse_location as wl", function ($join) {
                $join->on("wl.id", "=", "wp.warehouse_location_id")
                    ->on("wl.warehouse_id", "=", "wp.warehouse_id");
            })
            ->leftJoin("warehouse as w", "w.id", "=", "wp.warehouse_id")
            ->join("vendor_products as vp", "a.id", "=", "vp.product_id")
            ->select(
                "a.id",
                "a.name",
                "a.article_no",
                "a.mrp",
                "a.gst",
                "b.name as brand",
                "wp.warehouse_id",
                "wp.warehouse_location_id as location_id",
                "w.name as warehouse_name",
                "wl.location_code"
            )
            ->where('a.supplier_id', $request->user["supplier_id"])
            ->where('vp.vendor_id', $request->id)
            ->where("a.active", 1)
            ->get();


        $po =  DB::table("po_mst")->where("vendor_id", $request->id)->orderBy("id", "desc")->first();
        $po_mst = DB::table('po_mst')
            ->where('vendor_id', $request->id)
            ->where(function ($query) {
                $query->where('status', 'partial')
                    ->orWhere('status', 'generated');
            })
            ->get();

        return response()->json(["product" => $products, "po" => $po, "po_mst" => $po_mst]);
    }

    public function GetLastVendorPrice(Request $request)
    {
        $data = DB::table('po_det as d')
            ->join('po_mst as m', 'm.id', '=', 'd.mst_id')
            ->where('m.vendor_id', $request->vendor_id)
            ->where('d.product_id', $request->product_id)
            ->orderByDesc('d.id')
            ->select('d.price', 'm.created_at')
            ->first();
        return response()->json($data);
    }



    public function savePo(Request $request)
    {
        $company_settings =  DB::table("suppliers")->where("id", $request->user["supplier_id"])->first();
        $po_id = $company_settings->invoice_prefix . $company_settings->invoice_no;
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required',
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

        $discount_type = "flat";
        if ($request->discount_type == 1) {
            $discount_type = "percentage";
        }
        DB::beginTransaction();
        try {

            if ($request->id) {
                DB::table('po_mst')->where("id", $request->id)->update(array(
                    "vendor_id" => $request->vendor_id,
                    "supplier_id" => $request->user["supplier_id"],
                    // "po_id" => $po_id,
                    // "name" => $request->name,
                    "po_date" => $request->po_date,
                    "payment_term" => $request->payment_term,
                    "description" => $request->description,
                    "company_id" => $request->user["supplier_id"],
                    "status" => "pending",
                    "gst_type" => "CGST",
                    "expected_delivery_date" => $request->expected_delivery_date,
                    "loading_charges" => $request->loading_charges,
                    "freight_charges" => $request->freight_charges,
                    "discount_type" => $discount_type,
                    "discount_value" => $request->totalDiscount,
                    "round_off" => $request->roundOFF,
                ));
                $mst_id = $request->id;
            } else {
                $mst_id = DB::table('po_mst')->insertGetId(array(
                    "vendor_id" => $request->vendor_id,
                    "supplier_id" => $request->user["supplier_id"],
                    "po_id" => $po_id,
                    "po_date" => $request->po_date,
                    // "name" => $request->name,
                    "payment_term" => $request->payment_term,
                    "description" => $request->description,
                    "company_id" => $request->user["supplier_id"],
                    "status" => "pending",
                    "gst_type" => "CGST",
                    "expected_delivery_date" => $request->expected_delivery_date,
                    "loading_charges" => $request->loading_charges ?? 0,
                    "freight_charges" => $request->freight_charges ?? 0,
                    "discount_type" => $discount_type,
                    "discount_value" => $request->totalDiscount,
                    "round_off" => $request->roundOFF,
                ));
                $company_settings =  DB::table("suppliers")->where("id", $request->user["supplier_id"])->increment("invoice_no", 1);
            }
            DB::table("po_det")->where("mst_id", $mst_id)->delete();
            foreach ($prod_list as $key => $value) {
                DB::table('po_det')->insertGetId(array(
                    "mst_id" => $mst_id,
                    "supplier_id" => $request->user["supplier_id"],
                    "product_id" => $value->product_id,
                    "qty" => $value->qty,
                    "discount" => $value->discount,
                    "mrp" => $value->mrp,
                    "price" => $value->price,
                    "gst" => $value->gst,
                ));
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
        return redirect("/supplier/purchase-order-view/$mst_id")->with('success', "Save Successfully");
    }

    public function purchaseOrderView(Request $request, $id)
    {
        $orders = DB::table("po_mst as a")
            ->select("a.*", "b.name as vendor__name", "a.po_date", "b.number as vendor__number", "b.email as vendor_email", "b.address as vendor_address", "b.state as vendor_state", "b.city as vendor_city", "b.pincode as vendor_pincode", "b.fssai_no", "b.dealer_type", "b.gst as vendor_gst", "b.company as vendor_company", "b.address1 as line_1", "b.address2 as line_2", "c.*")
            ->join("vendor as b", "a.vendor_id", "b.id")
            ->join("suppliers as c", "a.supplier_id", "c.id")
            ->where("a.id", $id)->first();
        $po_det = DB::table("po_det as a")
            ->select("a.*", "b.name as name", "a.discount_type", "a.discount", "b.article_no", "c.name as uom", "b.description",  "d.name as brand", "b.cess_tax")
            ->leftJoin("products as b", "a.product_id", "b.id")
            ->leftJoin("product_brand as d", "b.brand_id", "d.id")
            ->leftJoin("product_uom as c", "b.uom_id", "c.id")
            ->where("a.mst_id", $id)
            ->get();
        return view("suppliers.purchase-order-view", compact("orders", "po_det"));
    }

    public function purchaseOrder(Request $request, $status)
    {
        $status = $request->status;
        $fromDt = request("fromDt");
        $toDt = request("toDt");


        $filter =  DB::table("po_mst as a")
            ->select("a.*", "b.company as vendor_name", "c.name as user_name")
            ->join("vendor as b", "a.vendor_id", "b.id")
            ->join("suppliers as c", "a.supplier_id", "c.id")
            ->where("a.status", $status)
            ->where("a.supplier_id", $request->user["supplier_id"]);

        if ($fromDt) {
            $filter->whereDate("po_date", ">=", $fromDt);
        }

        if ($toDt) {
            $filter->whereDate("po_date", "<=", $toDt);
        }


        $poList = $filter->orderBy("a.id", "desc")->get();
        return view("suppliers.purchase-order", compact("status", "poList"));
    }

    public function saveGeneratePO(Request $request)
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
            DB::table('po_mst')->where("id", $request->id)->update(array(
                "status" => "generated",
            ));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
        return redirect()->back()->with('success', "Save Successfully");
    }

    public function deletePO(Request $request)
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

            DB::table('po_mst')->where("id", $request->id)->delete();
            DB::table('po_det')->where("mst_id", $request->id)->delete();
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
        return redirect()->back()->with('success', "Save Successfully");
    }

    public function UploadPORequirementList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt',
        ]);
        if ($validator->fails()) {
            $messages = $validator->errors();
            $count = 0;
            foreach ($messages->all() as $error) {
                if ($count == 0)
                    return json_encode(['error' => $error]);
                $count++;
            }
        }
        $count_d = 0;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('csv', 'public');
            $csv = Reader::createFromPath(storage_path('app/public/' . $filePath), 'r');
            $data = [];
            foreach ($csv as $record) {
                if ($record[0] == "brand") {
                    continue;
                }
                $products = DB::table("products as a")
                    ->select("a.*", "b.name as brand_name")
                    ->join("product_brand as b", "a.brand_id", "b.id")
                    ->where('a.article_no', $record[1])->first();
                if ($products) {
                    $products->qty = $record[3];
                }
                $productIds = array_column($data, 'id');
                $data[] = $products;
            }
            return json_encode(['data' => $data]);
        }
        return json_encode(['error' => "No csv file selected for upload"]);
    }

    public function UploadNewPORequirementList(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
            'vendor_id' => 'required'
        ]);

        $vendorId = $request->vendor_id;

        $file = $request->file('file');
        $filePath = $file->store('csv', 'public');

        $csv = Reader::createFromPath(storage_path('app/public/' . $filePath), 'r');
        $csv->setHeaderOffset(0);

        $data = [];

        foreach ($csv->getRecords() as $record) {

            $isValid = 1;

            // ✅ Product
            $product = DB::table('products')
                ->where('article_no', trim($record['articleno']))
                ->first();

            if (!$product) {
                $isValid = 0;
            }

            // ✅ Vendor + Product mapping
            if ($isValid) {
                $vendorProduct = DB::table('vendor_products')
                    ->where('vendor_id', $vendorId)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$vendorProduct) {
                    $isValid = 0;
                }
            }

            // ✅ Warehouse
            $warehouse = DB::table('warehouse')
                ->where('name', trim($record['warehouse']))
                ->first();

            if (!$warehouse) {
                $isValid = 0;
            }
            $location = DB::table('warehouse_location')
                ->where('warehouse_id', $warehouse->id ?? 0)
                ->where('location_code', trim($record['locationcode']))
                ->first();

            if (!$location) {
                $isValid = 0;
            }

            $data[] = [
                'id'             => $product->id ?? 0,
                'name'           => $record['productname'],
                'article_no'     => $record['articleno'],
                'warehouse_id'   => $warehouse->id ?? 0,
                'warehouse_name' => $record['warehouse'],
                'location_id'    => $location->id ?? 0,
                'location_code'  => $record['locationcode'],
                'qty'            => (int)$record['qty'],
                'purchase_price' => $record['price'],
                'gst'            => $product->gst ?? 0,
                'is_valid'       => $isValid
            ];
        }

        return response()->json(['data' => $data]);
    }


    public function getLocation(Request $request)
    {
        return   WarehouseLocation::where("warehouse_id", $request->id)->get();
    }


    public function getLocationPurchase(Request $request)
    {

        return DB::table("warehouse_location as a")
            ->join("warehouse as b", "a.warehouse_id", "b.id")
            ->select(
                "a.id as location_id",
                "a.location_code",
                "b.id as warehouse_id",

            )
            ->where("a.location_code", "LIKE", "%{$request->q}%")
            ->where("b.supplier_id", $request->user["supplier_id"])
            ->limit(20)
            ->get();
    }

    public function GetPODet(Request $request)
    {
        $po_mst = DB::table("po_mst")
            ->select("id", "round_off")
            ->where("id", $request->id)
            ->first();
        $po_det = DB::table("po_det as a")
            ->select(
                "a.qty",
                "a.price",
                "a.discount",
                "a.discount_type",
                "a.received_qty",
                "b.id as product_id",
                "b.name as product_name",
                "b.gst",
                "b.article_no",
                "wl.id as location_id",
                "wl.location_code",
                "w.id as warehouse_id",
                "w.name as warehouse_name",
                "a.id",
                "a.mrp",

            )
            ->leftJoin("products as b", "a.product_id", "=", "b.id")
            ->leftJoin("product_brand as c", "b.brand_id", "=", "c.id")
            ->join("warehouse_product as wp", "wp.product_id", "=", "b.id")
            ->join("warehouse_location as wl", function ($join) {
                $join->on("wl.id", "=", "wp.warehouse_location_id")
                    ->on("wl.warehouse_id", "=", "wp.warehouse_id");
            })
            ->join("warehouse as w", "w.id", "=", "wl.warehouse_id")
            ->where("a.mst_id", $request->id)
            ->get();
        return response()->json([
            "status" => true,
            "round_off" => $po_mst->round_off ?? 0,
            "data" => $po_det
        ]);
    }

    // public function GetPODet(Request $request)
    // {
    //     $po_det = DB::table("po_det as a")
    //         ->select(
    //             "a.qty",
    //             "a.price",
    //             "a.discount",
    //             "a.discount_type",
    //             "a.received_qty",
    //             "b.id as product_id",
    //             "b.name as product_name",
    //             "b.gst",
    //             "b.article_no",
    //             "wl.id as location_id",
    //             "wl.location_code",
    //             "w.id as warehouse_id",
    //             "w.name as warehouse_name",
    //             "a.id",
    //             "a.mrp",

    //         )
    //         ->leftJoin("products as b", "a.product_id", "=", "b.id")
    //         ->leftJoin("product_brand as c", "b.brand_id", "=", "c.id")
    //         ->leftJoin("warehouse_product as wp", "wp.product_id", "=", "b.id")
    //         ->leftJoin("warehouse_location as wl", function ($join) {
    //             $join->on("wl.id", "=", "wp.warehouse_location_id")
    //                 ->on("wl.warehouse_id", "=", "wp.warehouse_id");
    //         })
    //         ->leftJoin("warehouse as w", "w.id", "=", "wl.warehouse_id")
    //         ->where("a.mst_id", $request->id)
    //         ->get();

    //     return response()->json($po_det);
    // }

    public function GetWarehouseLocations(Request $request)
    {
        $locations = DB::table('warehouse_location')
            ->where('warehouse_id', $request->warehouse_id)
            ->select('id', 'location_code')
            ->get();

        return response()->json($locations);
    }


    public function GetPO(Request $request)
    {
        $po_mst = DB::table('po_mst')
            ->where('vendor_id', $request->id)
            ->where(function ($query) {
                $query->where('status', 'partial')
                    ->orWhere('status', 'generated');
            })
            ->get();
        return $po_mst;
    }

    public function GetProducts1(Request $request)
    {
        $category_id = $request->brand_id;
        return DB::table('products')->where("category_id", $category_id)->get();
    }

    public function InwardStock(Request $request)
    {
        $vendor = Vendor::where('active', 1)->get();
        $warehouse = WareHouse::where('is_active', 1)->get();
        $warehouseZone = WareHouseZone::where('is_active', 1)->get();
        $product_category = DB::table('product_category')->where('supplier_id', $request->user['supplier_id'])->get();



        $id = request("id");
        $mst = collect();
        $det = collect();
        if ($id) {
            $mst = DB::table("stock_inward_mst")->where("id", $id)->first();
            $det = DB::table("stock_inward_det as a")
                ->select("a.*", "b.name",  "b.hsn_code", "b.article_no", "c.warehouse_id", "c.location_code as warehouse")
                ->join("products as b", "a.product_id", "b.id")
                ->join("warehouse_location as c", "a.location_id", "c.id")
                ->where("a.mst_id", $id)->get();
        }


        $data = compact('vendor', 'warehouse', 'warehouseZone', 'product_category', "mst", "det");


        return view("suppliers.inward-stock")->with($data);
    }

    public function SaveInwardStock(Request $request)
    {
        $inward_id = 'Inward_' . date('dmyhis');
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required',
            'invoice_file' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png'
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
        try {


            $invoiceFileName = null;

            if ($request->hasFile('invoice_file')) {
                $file = $request->file('invoice_file');
                $invoiceFileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('invoice-pdf'), $invoiceFileName);
            }

            $discount_type = "flat";
            if ($request->discount_type == 1) {
                $discount_type = "percentage";
            }
            if ($request->id) {


                $mst_id = $request->id;
                $stock_mst =  DB::table('stock_inward_mst')->where("id", $mst_id)->first();

                if (!$invoiceFileName) {
                    $invoiceFileName = $stock_mst->invoice_file;
                }

                DB::table('stock_inward_mst')->where("id", $mst_id)->update(array(
                    "vendor_id" => $request->vendor_id,
                    "invoice_no" => $request->invoice_no,
                    "invoice_date" => $request->invoice_date,
                    "received_material_date" => $request->received_material_date,
                    "description" => $request->description,
                    "invoice_file" => $invoiceFileName,
                    "supplier_id" => $request->user["supplier_id"],
                    "company_id" => $request->vendor_id,
                    "discount_type" => $discount_type,
                    "discount" => $request->totalDiscount,
                    "round_off" => $request->roundOFF,
                ));

                foreach ($prod_list as $key => $value) {


                    $detHistory =  DB::table('stock_inward_det_history')->where("stock_inward_det_id", $value->stock_inward_det_id)->first();

                    if ($detHistory) {
                        $stock_inward_det = DB::table("stock_inward_det")->where("po_det_id", $value->po_det_id)->where("mst_id", $mst_id)->where("product_id", $detHistory->product_id)->first();

                        if ($stock_inward_det) {

                            DB::table("po_det")->where("id", $value->po_det_id)->where("product_id", $detHistory->product_id)->decrement("received_qty", $stock_inward_det->qty);
                        }
                        $SIM =     DB::table('stock_inward_mst')->where("id", $mst_id)->first();

                        if ($SIM->is_current_stock == 1) {
                            DB::table("current_stock")->where("warehouse_id", $detHistory->warehouse_id)->where("location_id", $detHistory->location_id)->where("product_id", $detHistory->product_id)->decrement("stock", $detHistory->qty);

                            $CS =     DB::table("current_stock")->where("warehouse_id", $value->warehouse_id)->where("location_id", $value->location_id)->where("product_id", $value->product_id)->first();

                            if ($CS) {
                                DB::table("current_stock")->where("warehouse_id", $value->warehouse_id)->where("location_id", $value->location_id)->where("product_id", $value->product_id)->increment("stock", $value->qty);
                            } else {


                                DB::table("current_stock")->insert(array(
                                    "warehouse_id" => $value->warehouse_id,
                                    "location_id" => $value->location_id,
                                    "product_id" => $value->product_id,
                                    "stock" => $value->qty,
                                ));
                            }
                        }



                        DB::table("stock_inward_det")->where("mst_id", $mst_id)->where("product_id", $detHistory->product_id)->delete();
                    } else {

                        DB::rollBack();
                        return redirect()->back()->with("error", "Something went wrong");
                    }
                }
            } else {

                $exits = DB::table("stock_inward_mst")->where("supplier_id", $request->user["supplier_id"])
                    ->where("invoice_no", $request->invoice_no)->exists();
                if ($exits) {
                    return redirect()->back()->with('error', "Invoice no. already added");
                }
                $mst_id = DB::table('stock_inward_mst')->insertGetId(array(
                    "vendor_id" => $request->vendor_id,
                    "po_id" => $request->po_id,
                    "invoice_no" => $request->invoice_no,
                    "invoice_date" => $request->invoice_date,
                    "received_material_date" => $request->received_material_date,
                    "description" => $request->description,
                    "invoice_file" => $invoiceFileName,
                    "supplier_id" => $request->user["supplier_id"],
                    "company_id" => $request->vendor_id,
                    "discount_type" => $discount_type,
                    "discount" => $request->totalDiscount,
                    "round_off" => $request->roundOFF,
                ));
            }
            $status = 0;
            foreach ($prod_list as $key => $value) {

                if ($value->qty > 0) {


                    $stock_inward_det_id =  DB::table('stock_inward_det')->insertGetId([
                        "mst_id"       => $mst_id,
                        "product_id"   => $value->product_id,
                        "warehouse_id" => $value->warehouse_id,
                        "location_id"  => $value->location_id,
                        "qty"          => $value->qty,
                        "discount"     => $value->discount,
                        "price"        => $value->price,
                        "mrp"        => $value->mrp,
                        "gst"        => $value->gst,
                        "po_det_id"        => $value->po_det_id,
                    ]);

                    DB::table('stock_inward_det_history')->insertGetId([
                        "stock_inward_det_id"       => $stock_inward_det_id,
                        "product_id"   => $value->product_id,
                        "warehouse_id" => $value->warehouse_id,
                        "location_id"  => $value->location_id,
                        "qty"          => $value->qty,
                        "discount"     => $value->discount,
                        "price"        => $value->price,
                        "mrp"        => $value->mrp,
                        "gst"        => $value->gst,
                        "po_det_id"        => $value->po_det_id,
                        "user_id"        => $request->user["id"],
                    ]);
                    DB::table('po_det')->where("id", $value->po_det_id)->where("mst_id", $request->po_id)->where("product_id", $value->product_id)->increment("received_qty", $value->qty);



                    DB::table('warehouse_product')->updateOrInsert(
                        [
                            'product_id'   => $value->product_id,
                            'warehouse_id' => $value->warehouse_id,
                            'warehouse_location_id'  => $value->location_id,
                        ],
                        []
                    );
                }
            }



            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', $th->getMessage());
        }
        return redirect("supplier/inward-stock")->with('success', "Save Successfully");
    }

    public function inWardReport(Request $request)
    {

        $status = request("status");
        $fromDt = request("fromDt");
        $toDt = request("toDt");

        $filter =   DB::table("stock_inward_mst as a")
            ->select("a.*", "b.company as vendor", "c.po_id as po__id", "c.name as po_name",   "e.name as user")
            ->join("vendor as b", "a.vendor_id", "b.id")
            ->leftJoin("po_mst as c", "a.po_id", "c.id")
            ->join("suppliers as e", "a.supplier_id", "e.id");

        if ($status) {
            $is_current_stock = 0;
            if ($status == "approved") {
                $is_current_stock = 1;
            }
            $filter->where("a.is_current_stock", $is_current_stock);
        }
        if ($fromDt) {
            $filter->whereDate("invoice_date", ">=", $fromDt);
        }

        if ($toDt) {
            $filter->whereDate("invoice_date", "<=", $toDt);
        }
        $stock_inward_mst =   $filter->orderBy("a.id", "desc")
            ->get();




        return view("suppliers.inward-report", compact("stock_inward_mst"));
    }

    public function approveStockInward(Request $request)
    {
        $mst_id = $request->id;
        DB::beginTransaction();
        try {
            DB::table('stock_inward_mst')
                ->where('id', $mst_id)
                ->update([
                    'is_current_stock' => 1
                ]);
            $details = DB::table('stock_inward_det')
                ->where('mst_id', $mst_id)
                ->get();
            foreach ($details as $value) {
                $current_stock = DB::table("current_stock")
                    ->where("product_id", $value->product_id)
                    ->where("warehouse_id", $value->warehouse_id)
                    ->where("location_id", $value->location_id)
                    ->first();
                if ($current_stock) {
                    DB::table('current_stock')
                        ->where("id", $current_stock->id)
                        ->update([
                            'stock' => DB::raw('stock + ' . $value->qty)
                        ]);
                } else {
                    DB::table('current_stock')->insert([
                        "warehouse_id" => $value->warehouse_id,
                        "location_id"  => $value->location_id,
                        "product_id"   => $value->product_id,
                        "stock"        => $value->qty,
                    ]);
                }
            }
            DB::commit();
            return back()->with('success', 'MRN Approved & Stock Updated');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function InwardReportView(Request $request, $id)
    {

        $stock_inward_mst = DB::table("stock_inward_mst as a")
            ->select(
                "a.*",
                "a.invoice_no as inv",
                "b.company as vendor_name",
                "b.email as vendor_email",
                "b.gst as vendor_gst",
                "b.number as vendor_number",
                "b.address as vendor_address",
                "b.city as vendor_city",
                "b.state as vendor_state",
                "b.district as vendor_district",
                "b.pincode as vendor_pincode",
                "c.name as po_name",
                "d.name as supplier_name",
                "d.address as supplier_address",
                "d.number as supplier_phone",
                "d.email as supplier_email",
                "d.gst as supplier_gst",
                "a.id"
            )
            ->leftJoin("vendor as b", "a.vendor_id", "b.id")
            ->leftJoin("po_mst as c", "a.po_id", "c.id")
            ->leftJoin("suppliers as d", "a.supplier_id", "d.id")
            ->where("a.id", $id)
            ->where("a.supplier_id", $request->user["supplier_id"])
            ->first();
        $stock_inward_det = DB::table("stock_inward_det as a")
            ->select(
                "a.*",
                "b.name as product_name",
                "b.gst",
                "a.discount",
                "b.cess_tax",
                "b.article_no",
                "b.hsn_code",
                "c.name as uom",
                "d.name as brand",
                "w.name as warehouse_name",
                "l.location_code"
            )
            ->leftJoin("products as b", "a.product_id", "b.id")
            ->leftJoin("product_uom as c", "b.uom_id", "c.id")
            ->leftJoin("product_brand as d", "b.brand_id", "d.id")
            ->leftJoin("warehouse_product as wp", function ($join) {
                $join->on("a.product_id", "=", "wp.product_id")
                    ->on("a.warehouse_id", "=", "wp.warehouse_id")
                    ->on("a.location_id", "=", "wp.warehouse_location_id");
            })
            ->leftJoin("warehouse as w", "a.warehouse_id", "w.id")
            ->leftJoin("warehouse_location as l", "a.location_id", "l.id")
            ->where("a.mst_id", $id)
            ->get();
        // $stock_inward_det = DB::table("stock_inward_det as a")
        //     ->select(
        //         "a.*",
        //         "b.name as product_name",
        //         "b.gst",
        //         "b.cess_tax",
        //         "b.article_no",
        //         "b.hsn_code",
        //         "c.name as uom",
        //         "d.name as brand",
        //         "w.name as warehouse_name",
        //         "l.location_code"
        //     )
        //     ->leftJoin("products as b", "a.product_id", "b.id")
        //     ->leftJoin("product_uom as c", "b.uom_id", "c.id")
        //     ->leftJoin("product_brand as d", "b.brand_id", "d.id")
        //     ->leftJoin("warehouse_product as wp", "a.product_id", "wp.product_id")
        //     ->leftJoin("warehouse as w", "wp.warehouse_id", "w.id")
        //     ->leftJoin("warehouse_location as l", "wp.warehouse_location_id", "l.id")

        //     ->where("a.mst_id", $id)
        //     ->get();
        return view("suppliers.inward-report-view", compact("stock_inward_mst", "stock_inward_det"));
    }

    public function InwardReportSlip(Request $request, $id)
    {
        $stock_inward_mst = DB::table("stock_inward_mst as a")
            ->select(
                "a.*",
                "a.invoice_no as inv",
                "b.name as vendor_name",
                "b.email as vendor_email",
                "b.gst as vendor_gst",
                "b.number as vendor_number",
                "b.address as vendor_address",
                "b.city as vendor_city",
                "b.state as vendor_state",
                "b.district as vendor_district",
                "b.pincode as vendor_pincode",
                "c.name as po_name",
                "d.name as supplier_name",
                "d.address as supplier_address",
                "d.number as supplier_phone",
                "d.email as supplier_email",
                "d.gst as supplier_gst",
                "a.id"
            )
            ->leftJoin("vendor as b", "a.vendor_id", "b.id")
            ->leftJoin("po_mst as c", "a.po_id", "c.id")
            ->join("suppliers as d", "a.supplier_id", "d.id")
            ->where("a.id", $id)
            ->where("a.supplier_id", $request->user["supplier_id"])
            ->first();
        // $stock_inward_det = DB::table("stock_inward_det as a")
        //     ->select(
        //         "a.*",
        //         "b.name as product_name",
        //         "b.gst",
        //         "b.cess_tax",
        //         "b.article_no",
        //         "b.hsn_code",
        //         "c.name as uom",
        //         "d.name as brand",
        //         "w.name as warehouse_name",
        //         "l.location_code"
        //     )
        //     ->join("products as b", "a.product_id", "b.id")
        //     ->join("product_uom as c", "b.uom_id", "c.id")
        //     ->join("product_brand as d", "b.brand_id", "d.id")
        //     ->leftJoin("warehouse as w", "a.warehouse_id", "w.id")
        //     ->leftJoin("warehouse_location as l", "a.location_id", "l.id")
        //     ->where("a.mst_id", $id)
        //     ->get();
        $stock_inward_det = DB::table("stock_inward_det as a")
            ->select(
                "a.*",
                "b.name as product_name",
                "b.gst",
                "b.cess_tax",
                "b.article_no",
                "b.hsn_code",
                "c.name as uom",
                "d.name as brand",
                "w.name as warehouse_name",
                "l.location_code"
            )
            ->leftJoin("products as b", "a.product_id", "b.id")
            ->leftJoin("product_uom as c", "b.uom_id", "c.id")
            ->leftJoin("product_brand as d", "b.brand_id", "d.id")
            ->leftJoin("warehouse_product as wp", "a.product_id", "wp.product_id")
            ->leftJoin("warehouse as w", "wp.warehouse_id", "w.id")
            ->leftJoin("warehouse_location as l", "wp.warehouse_location_id", "l.id")

            ->where("a.mst_id", $id)
            ->get();

        return view("suppliers.inward-report-slip", compact("stock_inward_mst", "stock_inward_det"));
    }


    public function deleteStockInward(Request $request)
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
        DB::beginTransaction();
        try {
            $stock_inward_mst =  DB::table("stock_inward_mst")->where("id", $request->id)->first();
            $stock_inward_det =  DB::table("stock_inward_det")->where("mst_id", $request->id)->get();
            foreach ($stock_inward_det as $key => $value) {
                DB::table("current_stock")
                    ->where("product_id", $value->product_id)
                    ->where("location_id", $stock_inward_mst->location_id)
                    ->decrement("stock", $value->qty);
                DB::table("po_det")
                    ->where("product_id", $value->product_id)
                    ->where("mst_id", $stock_inward_mst->po_id)
                    ->decrement("received_qty", $value->qty);
            }
            DB::table("po_mst")->where("id", $stock_inward_mst->po_id)->update(array("status" => "partial"));
            DB::table("stock_inward_mst")->where("id", $request->id)->delete();
            DB::table("stock_inward_det")->where("mst_id", $request->id)->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
        return redirect()->back()->with('success', "Save Successfully");
    }

    public function PurchaseReturnList(Request $request)
    {
        $vendor = Vendor::where('active', 1)->get();
        $data =   DB::table("purchase_return_mst as a")
            ->select("a.*", "b.name as vendor", "b.company as company", "d.name as user", "e.invoice_no")
            ->join("vendor as b", "a.vendor_id", "b.id")
            ->join("users as d", "a.supplier_id", "d.id")
            ->join("stock_inward_mst as e", "a.inward_id", "e.id")
            ->where("a.supplier_id", $request->user['supplier_id'])
            ->orderBy("a.id", "desc")
            ->get();
        return view("suppliers.purchase-return", compact('vendor', 'data'));
    }

    public function GetInwardChallan(Request $request)
    {
        $data = DB::table("stock_inward_mst")->where("vendor_id", $request->id)->get();
        return response()->json($data);
    }

    public function GetInwardChallanProducts(Request $request)
    {

        $data = DB::table("stock_inward_det as a")
            ->select("a.*", "b.name as product", DB::raw("a.qty-a.return_qty as qty"))
            ->join("products as b", "a.product_id", "b.id")
            ->where("a.mst_id", $request->id)->get();
        return $data;
    }

    public function SavePurchaseReturn(Request $request)
    {
        $po_id = 'PO_' . date('dmyhis');
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required',
            'inward_id' => 'required',

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
        try {
            $mst_id = DB::table('purchase_return_mst')->insertGetId(array(
                "vendor_id" => $request->vendor_id,
                "supplier_id" => $request->user['supplier_id'],
                "inward_id" => $request->inward_id,
                "return_date" => $request->return_date,
                "description" => $request->description,
                "company_id" => $request->vendor_id,
            ));
            foreach ($prod_list as $key => $value) {
                DB::table('purchase_return_det')->insertGetId(array(
                    "mst_id" => $mst_id,
                    "product_id" => $value->product_id,
                    "qty" => $value->qty,
                ));
                DB::table('stock_inward_det')->where("mst_id", $request->inward_id)->where("product_id", $value->product_id)->increment("return_qty", $value->qty);
                DB::table('current_stock')->where("product_id", $value->product_id)->decrement("stock", $value->qty);
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
        return redirect()->back()->with('success', "Save Successfully");
    }

    public function PurchaseReturnChallanView(Request $request, $id)
    {
        $orders =   DB::table("purchase_return_mst as a")
            ->select("a.*", "b.name as vendor_name", "b.company as company", "d.name as user", "e.invoice_no", "b.address", "b.state", "b.city", "b.pincode", "b.number", "b.email", "b.gst",  "b.address as vendor_address", "b.number as vendor_number", "b.email as vendor_email", "b.gst as vendor_gst", 'b.city as vendor_city', 'b.state as vendor_state', 'b.pincode as vendor_pincode', 'd.name as supplier_name', 'd.email as supplier_email', 'd.number as supplier_number', 'd.address as supplier_address', 'd.gst as supplier_gst', 'e.id as po_id')
            ->join("vendor as b", "a.vendor_id", "b.id")
            ->join("suppliers as d", "a.supplier_id", "d.id")
            ->join("stock_inward_mst as e", "a.inward_id", "e.id")
            ->where("a.id", $id)
            ->first();

        $po_det = DB::table("purchase_return_det as a")
            ->select("a.*", "b.name as product_name", "b.article_no", "b.hsn_code", "c.name as uom", 'd.price as price', 'b.gst as gst', 'b.cess_tax')
            ->join("products as b", "a.product_id", "b.id")
            ->join("product_uom as c", "b.uom_id", "c.id")
            ->join("stock_inward_det as d", "d.product_id", "a.id")
            ->where("a.mst_id", $id)
            ->get();
        return view("suppliers.purchase-return-challan-view", compact("orders", "po_det"));
    }

    public function inwardProductWise(Request $request)
    {
        $product_id = request("product_id");
        $vendor_id = request("vendor_id");
        $fromDt = request("fromDt");
        $toDt = request("toDt");

        $filter = DB::table("stock_inward_mst as a")
            ->select(
                "a.id",
                "a.invoice_no",
                "a.invoice_date",
                "a.received_material_date",
                "a.description",
                "a.created_at",

                "b.company as vendor",
                "c.po_id as po_name",
                "e.name as user",

                "g.name as product_name",
                "g.article_no",

                "f.qty",
                "f.price",

                "h.name as warehouse",
                "l.location_code as location",

                DB::raw("(f.qty * f.price) as total")
            )
            ->join("vendor as b", "a.vendor_id", "=", "b.id")
            ->leftJoin("po_mst as c", "a.po_id", "=", "c.id")
            ->join("suppliers as e", "a.supplier_id", "=", "e.id")
            ->join("stock_inward_det as f", "a.id", "=", "f.mst_id")
            ->join("products as g", "f.product_id", "=", "g.id")

            ->leftJoin("warehouse_location as l", "f.location_id", "=", "l.id")
            ->leftJoin("warehouse as h", "l.warehouse_id", "=", "h.id");
        if ($product_id) {
            $filter->where("f.product_id", $product_id);
        }
        if ($vendor_id) {
            $filter->where("a.vendor_id", $vendor_id);
        }
        if ($fromDt) {
            $filter->whereDate("a.invoice_date", ">=", $fromDt);
        }
        if ($toDt) {
            $filter->whereDate("a.invoice_date", "<=", $toDt);
        }


        $data =  $filter->where("a.supplier_id", $request->user['supplier_id'])
            ->orderBy("a.id", "desc")
            ->get();

        $vendor = DB::table("vendor")->where("supplier_id", $request->user["supplier_id"])->get();
        $product = DB::table("products")->where("supplier_id", $request->user["supplier_id"])->get();

        return view("suppliers.inward-product-wise", compact("data", "vendor","product"));
    }

    public function CurrentStock(Request $request)
    {
        $current_stock = DB::table('current_stock as a')
            ->select(
                "a.id",
                "a.product_id",
                "b.name as product",
                "b.article_no",
                "c.name as warehouse",
                "d.location_code",
                DB::raw("SUM(a.stock) as total_stock")
            )
            ->leftJoin("products as b", "a.product_id", "=", "b.id")
            ->leftJoin("warehouse as c", "a.warehouse_id", "=", "c.id")
            ->leftJoin("warehouse_location as d", "a.location_id", "=", "d.id")
            ->where("b.supplier_id", $request->user['supplier_id'])
            ->groupBy("a.id", "a.product_id", "a.warehouse_id", "b.name", "b.article_no", "c.name", "d.location_code")
            ->orderBy("c.name")
            ->get();
        return view("suppliers.current-stock", compact("current_stock"));
    }


    public function getPOProducts(Request $request)
    {

        return DB::table("products as a")
            ->leftJoin("warehouse_product as b", "a.id", "b.product_id")
            ->leftJoin("warehouse_location as c", "b.warehouse_location_id", "c.id")
            ->select(
                "a.name as product_name",
                "a.id as product_id",
                "c.location_code",
                "c.warehouse_id as warehouse_id",
                "c.id as location_id",

            )
            ->where("c.location_code", "LIKE", "%{$request->q}%")
            ->orWhere("a.name", "LIKE", "%{$request->q}%")
            ->where("a.supplier_id", $request->user["supplier_id"])
            ->limit(20)
            ->get();
    }

    public function checkInvoiceNo(Request $request)
    {

        $check = DB::table("stock_inward_mst")->where("invoice_no", $request->invoice_id)->first();
        if ($check) {
            return true;
        } else {
            return false;
        }
    }
    // public function CurrentStockHistory(Request $request, $id)
    // {
    //     $current_stock = DB::table('current_stock as a')
    //         ->select(
    //             "a.product_id",
    //             "b.name as product",
    //             "b.article_no",
    //             "c.name as warehouse",
    //             "d.location_code",
    //             DB::raw("SUM(a.stock) as total_stock")
    //         )
    //         ->leftJoin("products as b", "a.product_id", "=", "b.id")
    //         ->leftJoin("warehouse as c", "a.warehouse_id", "=", "c.id")
    //         ->leftJoin("warehouse_location as d", "a.location_id", "=", "d.id")
    //         ->where("b.supplier_id", $request->user['supplier_id'])
    //         ->groupBy("a.product_id", "a.warehouse_id", "b.name", "b.article_no", "c.name", "d.location_code")
    //         ->orderBy("c.name")
    //         ->get();
    //     return view("suppliers.current-stock-history", compact("current_stock"));
    // }
}
