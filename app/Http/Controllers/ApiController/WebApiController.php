<?php

namespace App\Http\Controllers\ApiController;

use App\Http\Controllers\Controller;
use App\Services\checkGST;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WebApiController extends Controller
{
    public function BannerApi(Request $request)
    {
        try {
            $data = DB::table("sliders1")
                ->select(
                    "sliders1.*",
                    DB::raw("CONCAT('https://store.bulkbasketindia.com/sliders/', image) as image")
                )
                ->whereNotNull("image")
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching sliders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkGSTApi(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'gst_no' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Enter valid GST NO.',
                "data" => []
            ], 422);
        }

        $gstNumber = $request->gst_no;

        $gstService = new checkGST();
        return  $gstService->generate($gstNumber);
    }

    public function homeCategory(Request $request)
    {
        $supplier_id = $request->user["supplier_id"] ?? 0;
        $customer_id = $request->user["customer_id"] ?? 0;
        try {
            $filter = DB::table('product_brand')
                ->select(
                    'id',
                    'name',
                    'image',
                    DB::raw("CONCAT('https://store.bulkbasketindia.com/master images/',image) as image")
                );
            if ($supplier_id > 0) {
                $filter->where('supplier_id', $supplier_id);
            }
            $filter = DB::table("products as a")
                ->leftJoin("customers_products_list as b", function ($join) use ($customer_id, $supplier_id) {
                    $join->on("a.id", "=", "b.product_id")
                        ->where("b.customer_id", $customer_id)
                        ->where("a.supplier_id", $supplier_id);
                })
                ->join("product_sub_category as psc", "a.sub_category_id", "psc.id")
                ->where("a.is_home", 1)
                ->where("a.active", 1);
            $products = $filter->select(
                "a.id",
                "a.name",
                "a.gst",
                "a.cess_tax",
                "a.mrp",
                "psc.name as sub_category",
                DB::raw("COALESCE(b.base_price, a.base_price) as price"),
                DB::raw("
                        CASE 
                            WHEN a.image IS NOT NULL AND a.image != '' 
                            THEN CONCAT('https://store.bulkbasketindia.com/product images/', a.image) 
                            ELSE NULL 
                        END as image
                    ")
            )->get();
            $productIds = $products->pluck("id")->toArray();
            $customerId = $customer_id;
            $carts = DB::table("cart")
                ->where("customer_id", $customerId)
                ->whereIn("product_id", $productIds)
                ->get()
                ->keyBy("product_id");
            $wishlists = DB::table("wishlist")
                ->where("customer_id", $customerId)
                ->whereIn("product_id", $productIds)
                ->get()
                ->keyBy("product_id");
            $customerTiers = DB::table("customers_products_list as a")
                ->join("customers_products_tier as b", "a.id", "b.customer_product_id")
                ->select("a.product_id", "b.qty", "b.base_price as price")
                ->where("a.customer_id", $customerId)
                ->whereIn("a.product_id", $productIds)
                ->orderBy("b.qty", "asc")
                ->get()
                ->groupBy("product_id");
            $defaultTiers = DB::table("product_price")
                ->select("product_id", "qty", "price")
                ->whereIn("product_id", $productIds)
                ->orderBy("qty", "asc")
                ->get()
                ->groupBy("product_id");
            $productArr = [];
            foreach ($products as $product) {
                $tiers = $customerTiers[$product->id] ?? $defaultTiers[$product->id] ?? collect();
                $cart = $carts[$product->id] ?? null;
                $wishlist = $wishlists[$product->id] ?? null;
                $productData = [
                    "id" => $product->id,
                    "image" => $product->image,
                    "name" => $product->name,
                    "gst" => $product->gst,
                    "cess_tax" => $product->cess_tax,
                    "price" => $product->price,
                    "mrp" => $product->mrp,
                    "discount" => $product->mrp > 0
                        ? round((($product->mrp - $product->price) / $product->mrp) * 100, 2)
                        : 0,
                    "tiers" => $tiers->values(),
                    "cart" => $cart,
                    "cart_status" => $cart ? true : false,
                    "wishlist_status" => $wishlist ? true : false,
                ];
                $productArr[$product->sub_category]["sub_category"] = $product->sub_category;
                $productArr[$product->sub_category]["products"][] = $productData;
            }
            $data["products"] = array_values($productArr);
            return response()->json([
                'error' => false,
                'message' => "Fetch successfully",
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error fetching data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'qty' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                "data" => []
            ], 422);
        }


        try {
            $cart =   DB::table("cart")->where("customer_id", $request->user["customer_id"])->where("product_id", $request->product_id)->first();
            if ($cart) {
                return response()->json([
                    'error' => true,
                    'message' => "Already added",
                    "data" => []
                ], 422);
            }
            DB::table("cart")->insert(array(
                "customer_id" => $request->user["customer_id"],
                "product_id" => $request->product_id,
                "qty" => $request->qty
            ));

            return response()->json([
                'error' => false,
                'message' => "save Successfully",
                "data" => []
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function getCart(Request $request)
    {
        try {

            $cart = DB::table("cart as c")
                ->join("products as p", "c.product_id", "p.id")
                ->where("c.customer_id", $request->user["customer_id"])
                ->select(
                    "c.id",
                    "c.product_id",
                    "c.qty",
                    "p.name",
                    "p.mrp",
                    "p.gst",
                    "p.cess_tax",
                    DB::raw("
            CASE 
                WHEN p.image IS NOT NULL AND p.image != '' 
                THEN CONCAT('https://store.bulkbasketindia.com/product images/', p.image) 
                ELSE NULL 
            END as image
        ")
                )
                ->get();

            $productIds = $cart->pluck('product_id');

            $productPrices = DB::table("product_price")
                ->whereIn("product_id", $productIds)
                ->orderBy("qty", "asc")
                ->get()
                ->groupBy("product_id");

            $result = [];

            foreach ($cart as $item) {
                $qty = $item->qty;
                $details = $productPrices[$item->product_id] ?? collect();
                $price = DB::table("customers_products_list as a")
                    ->join("customers_products_tier as b", "a.id", "b.customer_product_id")
                    ->where("a.customer_id", $request->user["customer_id"])
                    ->where("a.product_id", $item->product_id)
                    ->where("b.qty", "<=", $qty)
                    ->orderBy("b.qty", "desc")
                    ->value("b.base_price"); 
                if (!$price) {

                    $price = DB::table("product_price")
                        ->where("product_id", $item->product_id)
                        ->where("qty", "<=", $qty)
                        ->orderBy("qty", "desc")
                        ->value("price");
                } 
                if (!$price) {
                    $price = DB::table("products")->where("id", $item->product_id)->value("base_price");
                }

                $result[] = [
                    "cart_id" => $item->id,
                    "product_id" => $item->product_id,
                    "name" => $item->name,
                    "image" => $item->image,
                    "qty" => $qty,
                    "price" => $price,
                    "gst" => $item->gst,
                    "cess_tax" => $item->cess_tax,
                    "mrp" => $item->mrp,
                    "total" => $price * $qty, 
                    "price_tiers" => $details->values(),
                    "discount" => $item->mrp > 0
                        ? round((($item->mrp - $price) / $item->mrp) * 100, 2)
                        : 0
                ];
            }
            $taxable = 0;
            $totalAmount = 0;
            $gstBifurcation = [];

            foreach ($result as $value) {

                $amount = $value["price"] * $value["qty"]; // taxable amount
                $gst = $value["gst"]; // 5,12,18

                $taxable += $amount;

                $gstAmount = ($amount * $gst) / 100;

                if (!isset($gstBifurcation[$gst])) {
                    $gstBifurcation[$gst] = (object)[
                        "percentage" => number_format($gst, 2),
                        "price" => 0
                    ];
                }

                $gstBifurcation[$gst]->price += $gstAmount;




                $totalAmount += $amount + $gstAmount;
            }
            $gstBifurcation = array_values($gstBifurcation);
            $orderSummary = array("taxable" => $taxable, "gstBifurcation" => $gstBifurcation, "totalAmount" => $totalAmount);


            return response()->json([
                'error' => false,
                'message' => "Load Successfully",
                "data" => $result,
                "orderSummary" => $orderSummary
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function removeCartItem(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'cart_id' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                "data" => []
            ], 422);
        }


        try {

            DB::table("cart")->where("id", $request->cart_id)->delete();
            return response()->json([
                'error' => false,
                'message' => "Remove Successfully",
                "data" => []
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function updateCartQty(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'qty' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                "data" => []
            ], 422);
        }


        try {

            $cart =   DB::table("cart")
                ->where("product_id", $request->product_id)
                ->where("customer_id", $request->user["customer_id"])
                ->first();

            if (!$cart) {
                return response()->json([
                    'error' => true,
                    'message' => "Item not found",
                    "data" => []
                ], 404);
            }

            if ($request->qty > 0) {
                DB::table("cart")->where("id", $cart->id)->update(array(
                    "qty" => $request->qty
                ));
            } else {
                DB::table("cart")->where("id", $cart->id)->delete();
            }


            return response()->json([
                'error' => false,
                'message' => "Save Successfully",
                "data" => []
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function addToWishList(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                "data" => []
            ], 422);
        }


        try {
            $wishlist =   DB::table("wishlist")->where("customer_id", $request->user["customer_id"])->where("product_id", $request->product_id)->first();
            if ($wishlist) {
                return response()->json([
                    'error' => true,
                    'message' => "Already added",
                    "data" => []
                ], 422);
            }
            DB::table("wishlist")->insert(array(
                "customer_id" => $request->user["customer_id"],
                "product_id" => $request->product_id,
                "qty" => 1
            ));

            return response()->json([
                'error' => false,
                'message' => "save Successfully",
                "data" => []
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function updateWishListQty(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'qty' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                "data" => []
            ], 422);
        }


        try {

            $cart =   DB::table("wishlist")
                ->where("product_id", $request->product_id)
                ->where("customer_id", $request->user["customer_id"])
                ->first();

            if (!$cart) {
                return response()->json([
                    'error' => true,
                    'message' => "Item not found",
                    "data" => []
                ], 404);
            }

            if ($request->qty > 0) {
                DB::table("wishlist")->where("id", $cart->id)->update(array(
                    "qty" => $request->qty
                ));
            } else {
                DB::table("wishlist")->where("id", $cart->id)->delete();
            }


            return response()->json([
                'error' => false,
                'message' => "Save Successfully",
                "data" => []
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function getWishList(Request $request)
    {
        try {
            $wishlist = DB::table("wishlist as c")
                ->join("products as p", "c.product_id", "p.id")
                ->where("c.customer_id", $request->user["customer_id"])
                ->select(
                    "c.id",
                    "c.product_id",
                    "c.qty",
                    "p.name",
                    "p.mrp",
                    "p.gst",
                    "p.cess_tax",
                    DB::raw("
            CASE 
                WHEN p.image IS NOT NULL AND p.image != '' 
                THEN CONCAT('https://store.bulkbasketindia.com/product images/', p.image) 
                ELSE NULL 
            END as image
        ")
                )
                ->get();

            return response()->json([
                'error' => false,
                'message' => "Load Successfully",
                "data" => $wishlist
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $customer =  DB::table("customers as a")
                ->select("a.*")
                ->join("customer_users as b", "a.id", "b.customer_id")->where("b.id", $request->user["id"])->first();
            $image = "";
            if ($request->hasFile('image')) {
                $image = time() . '.' . $request->file('image')->extension();
                $request->file('image')->move(public_path('customer'), $image);
            } else {
                $image = $customer->image;
            }


            if ($customer) {

                DB::table("customers as a")
                    ->join("customer_users as b", "a.id", "b.customer_id")
                    ->where("b.id", $request->user["id"])
                    ->update(array(
                        "a.name" => $request->name ?? $customer->name,
                        "a.number" => $request->number ??  $customer->number,
                        "a.email" => $request->email ??  $customer->email,
                        "a.gst" => $request->gst ?? $customer->gst,
                        "a.address" => $request->address ?? $customer->address,
                        "a.state" => $request->state ?? $customer->state,
                        "a.district" => $request->district ?? $customer->district,
                        "a.city" => $request->city ?? $customer->city,
                        "a.pincode" => $request->pincode ?? $customer->pincode,
                        "a.image" => $image ?? $customer->image,

                    ));

                return response()->json([
                    'error' => false,
                    'message' => "Update Successfully",
                    "data" => $customer
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => "Customer details not found",
                    "data" => []
                ], 422);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function getProfile(Request $request)
    {
        try {

            $customer =  DB::table("customers as a")
                ->select("a.*", DB::raw("
                        CASE 
                            WHEN a.image IS NOT NULL AND a.image != '' 
                            THEN CONCAT('https://store.bulkbasketindia.com/customer/', a.image) 
                            ELSE NULL 
                        END as image
                    "))
                ->join("customer_users as b", "a.id", "b.customer_id")->where("b.id", $request->user["id"])->first();

            if ($customer) {

                return response()->json([
                    'error' => false,
                    'message' => "Load Successfully",
                    "data" => $customer
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => "Customer details not found",
                    "data" => []
                ], 422);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function saveAddress(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'address_line_1' => 'required',
            'state' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                "data" => []
            ], 422);
        }

        try {

            $field = array(
                "customer_id" => $request->user["customer_id"],
                "address_line_1" => $request->address_line_1,
                "address_line_2" => $request->address_line_2,
                "address" => $request->address,
                "state" => $request->state,
                "district" => $request->district,
                "city" => $request->city,
                "pincode" => $request->pincode,
                "coordinates" => $request->coordinates,
            );

            if ($request->id) {
                DB::table("customer_address")->where("id", $request->id)->update($field);
            } else {
                DB::table("customer_address")->insert($field);
            }

            $count = DB::table("customer_address")
                ->where("customer_id", $request->user["customer_id"])
                ->count();

            if ($count == 1) {
                DB::table("customer_address")
                    ->where("customer_id", $request->user["customer_id"])
                    ->update(["default_status" => 1]);
            }
            $data =   DB::table("customer_address")->where("customer_id", $request->user["customer_id"])->get();

            return response()->json([
                'error' => false,
                'message' => "Save Successfully",
                "data" => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function getAddress(Request $request)
    {
        try {
            $data =    DB::table("customer_address")->where("customer_id", $request->user["customer_id"])->orderBy("default_status", "desc")->get();

            return response()->json([
                'error' => false,
                'message' => "Load Successfully",
                "data" => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }


    public function updateDefaultAddress(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'address_id' => 'required',


        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                "data" => []
            ], 422);
        }

        try {
            DB::table("customer_address")->where("customer_id", $request->user["customer_id"])->update(array(
                "default_status" => 0
            ));

            if ($request->address_id) {
                DB::table("customer_address")->where("id", $request->address_id)->update(array("default_status" => 1));
            }

            $data =   DB::table("customer_address")->where("customer_id", $request->user["customer_id"])->orderby("default_status", "desc")->get();

            return response()->json([
                'error' => false,
                'message' => "Save Successfully",
                "data" => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function getStates(Request $request)
    {
        try {
            $data =  DB::table("state_city")->select("state")->distinct("state")->get();

            return response()->json([
                'error' => false,
                'message' => "Load Successfully",
                "data" => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function getDistrict(Request $request, $state)
    {
        try {
            $data =  DB::table("state_city")->select("city")->where("state", $state)->get();
            return response()->json([
                'error' => false,
                'message' => "Load Successfully",
                "data" => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }


    public function deleteAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                "data" => []
            ], 422);
        }

        try {
            DB::table("customer_address")->where("id", $request->id)->delete();
            return response()->json([
                'error' => false,
                'message' => "Save Successfully",
                "data" => []
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }
}
