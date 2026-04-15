<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerProductPrice extends Controller
{

    public function customerProductSearch(Request $request)
    {
        $products = DB::table('products')
            ->where('active', 1)
            ->where('name', 'like', '%' . $request->q . '%')
            ->select('id', 'name', 'mrp', 'base_price')
            ->limit(10)
            ->get();
        return response()->json($products);
    }

    public function customerProductAdd(Request $request, $id)
    {

        return view('suppliers.customer-product-price', compact('id'));
    }

    public function store(Request $request)
    {
        foreach ($request->products as $item) {
            DB::table('customers_products_list')->insert([
                'customer_id' => $request->customer_id,
                'supplier_id' => $request->user['supplier_id'],
                'product_id'  => $item['product_id'],
                'base_price'  => $item['base_price'],
            ]);
        }

        return response()->json(['status' => true]);
    }

    public function customerProductList($customer_id)
    {
        $products = DB::table('customers_products_list as cpl')
            ->join('products as p', 'p.id', '=', 'cpl.product_id')
            ->leftJoin('product_brand as b', 'b.id', '=', 'p.brand_id')
            ->leftJoin('product_category as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('product_sub_category as sc', 'sc.id', '=', 'p.sub_category_id')
            ->leftJoin('product_sub_sub_category as ssc', 'ssc.id', '=', 'p.product_sub_sub_category')
            ->where('cpl.customer_id', $customer_id)
            ->select(
                'cpl.id as cpl_id',
                'p.name',
                'cpl.base_price',
                'p.mrp',
                'p.hsn_code',
                'p.gst',
                'b.name as brand_name',
                'c.name as category_name',
                'sc.name as sub_category_name',
                'ssc.name as sub_subcategory_name'
            )
            ->get();
        return view('suppliers.customer-product-list', compact('products', 'customer_id'));
    }

    public function AddCustomerProductPrice(Request $request)
    {
        try {
            DB::table("customers_products_tier")->insert(array(
                "customer_product_id" => $request->customer_product_id,
                "base_price" => $request->product_price,
                "qty" => $request->product_qty,
            ));
            return response()->json(["msg" => "Save successfully", "error" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["msg" => $th->getMessage(), "error" => "error"]);
        }
    }

    public function GetCustomerProductPrices(Request $request)
    {

        return DB::table("customers_products_tier")->where("customer_product_id", $request->id)->get();
    }

    public function DeleteCustomerProductPrice(Request $request)
    {
        try {
            DB::table("customers_products_tier")->where("id", $request->id)->delete();
            return response()->json(["msg" => "Save successfully", "error" => "success"]);
        } catch (\Throwable $th) {
            return response()->json(["msg" => $th->getMessage(), "error" => "error"]);
        }
    }

    public function getOrderProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required',
            'customer_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => true, "msg" => $validator->errors()->first(), "data" => ""], 400);
        }

        try {
            $query = DB::table("products as a")
                ->select(
                    "a.*",
                    DB::raw("(SELECT COALESCE(SUM(cs.stock), 0)
                FROM current_stock cs
                WHERE cs.product_id = a.id) as current_stock"),
                )
                ->where("a.supplier_id", $request->user["supplier_id"]);

            $words = explode(' ', strtolower($request->search));

            foreach ($words as $word) {
                $query->whereRaw("LOWER(a.name) LIKE ?", ["%$word%"]);
            }

            $data = $query->get();

            if ($data) {
                return response()->json(["error" => false, "msg" => "Success", "data" => $data], 200);
            } else {
                return response()->json(["error" => true, "msg" => "No Data Found", "data" => ""], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(["error" => true, "msg" => $th->getMessage(), "data" => ""], 400);
        }
    }


    public function getProductPrice(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => true, "msg" => $validator->errors()->first(), "data" => ""], 400);
        }

        try {
            $data = DB::table("products as a")
                ->leftJoin("customers_products_list as b", function ($join) use ($request) {
                    $join->on("a.id", "=", "b.product_id")
                        ->where("b.customer_id", "=", $request->customer_id);
                })
                ->where("a.supplier_id", $request->user["supplier_id"])
                ->where("a.id", $request->product_id)
                ->select(
                    "a.id as product_id",
                    "a.name as name",
                    "a.gst",
                    "a.cess_tax",
                    DB::raw("(SELECT COALESCE(SUM(cs.stock), 0)
                    FROM current_stock cs
                    WHERE cs.product_id = a.id) as current_stock"),
                    DB::raw("COALESCE(b.base_price, a.base_price) as price"),

                )
                ->first();

            if ($data) {
                return response()->json(["error" => false, "msg" => "Success", "data" => $data], 200);
            } else {
                return response()->json(["error" => true, "msg" => "No Data Found", "data" => ""], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(["error" => true, "msg" => $th->getMessage(), "data" => ""], 400);
        }
    }

    public function getProductQtyWisePrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'qty' => 'required',
            'customer_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => true, "msg" => $validator->errors()->first(), "data" => ""], 400);
        }

        try {
            $customerPrice = DB::table("customers_products_list as a")
                ->select("b.base_price as price")
                ->join("customers_products_tier as b", "a.id", "b.customer_product_id")
                ->where("a.customer_id", $request->customer_id)
                ->where("a.product_id", $request->product_id)
                ->where("b.qty", "<=", $request->qty)
                ->orderBy("b.qty", "desc")
                ->first();

            $productPrice = null;

            if (!$customerPrice) {

                $productPrice = DB::table("product_price")
                    ->select("price")
                    ->where("product_id", $request->product_id)
                    ->where("qty", "<=", $request->qty)
                    ->orderBy("qty", "desc")
                    ->first();


                if (!$productPrice) {

                    $productPrice = DB::table("customers_products_list")
                        ->select("base_price as price")
                        ->where("customer_id", $request->customer_id)
                        ->where("product_id", $request->product_id)
                        ->first();


                    if (!$productPrice) {
                        $productPrice = DB::table("products")
                            ->select("base_price as price")
                            ->where("id", $request->product_id)
                            ->first();
                    }
                }
            }

            $data = $customerPrice ?? $productPrice;

            if ($data) {
                return response()->json(["error" => false, "msg" => "Success", "data" => $data], 200);
            } else {
                return response()->json(["error" => true, "msg" => "No Data Found", "data" => ""], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(["error" => true, "msg" => $th->getMessage(), "data" => ""], 400);
        }
    }
}
