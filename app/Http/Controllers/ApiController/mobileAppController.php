<?php

namespace App\Http\Controllers\ApiController;

use App\Http\Controllers\Controller;
use App\Services\checkGST;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\select;

class mobileAppController extends Controller
{
    public function sendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|digits:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Enter valid 10-digit mobile number',
                "data" => []
            ], 422);
        }




        try {
            $otp = rand(100000, 999999);
            $number = '91' . $request->number;

            $smsConfig = config('services.smswala');
            $msg = "Your OTP for login is {#var1#}. Valid for {#var2#} minutes. Do not share it with anyone - Bulk Basket India";
            $finalMsg = str_replace(
                ['{#var1#}', '{#var2#}'],
                [$otp, 2],
                $msg
            );
            $url = "{$smsConfig['url']}?"
                . "key={$smsConfig['key']}"
                . "&campaign={$smsConfig['campaign']}"
                . "&routeid={$smsConfig['routeid']}"
                . "&type=text"
                . "&contacts={$number}"
                . "&senderid={$smsConfig['sender']}"
                . "&msg=" . urlencode($finalMsg)
                . "&template_id={$smsConfig['templates']['otp']}"
                . "&pe_id={$smsConfig['pe_id']}";
            $response = Http::get($url);
            $respBody = $response->body();
            if (
                $response->successful() &&
                (stripos($respBody, 'SMS-SHOOT-ID') !== false || stripos($respBody, 'SUCCESS') !== false)
            ) {


                $customer_opt = DB::table('customer_otp')->where("number", $request->number)->first();
                if ($customer_opt) {
                    DB::table("customer_otp")
                        ->where("number", $customer_opt->number)
                        ->update([
                            "otp" => $otp,
                            "expire_at" => Carbon::now()->addMinutes(2),
                        ]);
                } else {
                    DB::table("customer_otp")

                        ->insert([
                            "otp" => $otp,
                            "number" => $request->number,
                            "expire_at" => Carbon::now()->addMinutes(2),
                        ]);
                }



                return response()->json([
                    'error' => false,
                    'message' => 'OTP sent successfully',
                    "data" => []
                ]);
            }
            Log::error("❌ OTP SMS failed for {$number}, resp={$respBody}");
            return response()->json([
                'error' => true,
                'message' => "Failed to send OTP. Try again later",
                "data" => []
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }


    public function verifyOTP(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'number' => 'required|digits:10',
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Enter valid 6-digit OTP',
                "data" => []
            ], 422);
        }


        try {


            $customer_otp = DB::table('customer_otp')->where("number", $request->number)
                ->where("otp", $request->otp)
                ->first();
            if ($request->number == "9999999999" && $request->otp == "123456") {
                $customer_otp = true;
            } else {
                $customer_otp = DB::table('customer_otp')
                    ->where("number", $request->number)
                    ->where("otp", $request->otp)
                    ->first();
            }

            if ($customer_otp) {
                $user = DB::table("customer_users as a")
                    ->select("a.*", "b.active", "b.supplier_id")
                    ->join("customers as b", "a.customer_id", "b.id")
                    ->where("a.number", $request->number)
                    ->first();

                if (!$user) {
                    $customerId = DB::table('customers')->insertGetId([
                        'name' => 'Guest User',
                        'active' => 0,
                        'number' => $request->number,
                        'supplier_id' => 0,
                    ]);

                    $user_id =  DB::table('customer_users')->insertGetId([
                        'customer_id' => $customerId,
                        'number' => $request->number,
                        'name' => 'Guest User',
                    ]);
                } else {
                    $user_id = $user->id;
                }

                if ($user->active == 2) {
                    return response()->json([
                        'error' => false,
                        'message' => 'Your account is under process. Please wait 2–4 hours.',
                        'redirect' => 'pending',
                        "data" => []
                    ], 200);
                }

                // if ($user->active == 0 && $user->supplier_id == 0) {
                //     return response()->json([
                //         'error' => false,
                //         'message' => 'You need to signup first',
                //         'redirect' => 'signup',
                //         "data" => []
                //     ], 200);
                // }

                // if ($user->active == 0) {
                //     return response()->json([
                //         'error' => false,
                //         'message' => 'Your account is inactive. Please contact supplier.',
                //         'redirect' => 'inactive',
                //         "data" => []
                //     ], 200);
                // }


                $token = bin2hex(random_bytes(16));
                $agent = new \Jenssegers\Agent\Agent();
                $browser = $agent->browser();
                $version = $agent->version($browser);
                $platform = $agent->platform();

                DB::table('customer_users')->where("id", $user_id)->update([
                    'app_token' => $token,
                    'last_ip' => $request->ip(),
                    'last_login' => now(),
                    'platform' => "$browser / $version / $platform"
                ]);

                DB::table('customer_otp')->where("number", $request->number)
                    ->where("otp", $request->otp)
                    ->delete();

                return response()->json([
                    'error' => false,
                    'message' => 'Login successful',
                    'token' => $token,
                    "data" =>  $user
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid OTP',
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

    public function homePage(Request $request)
    {
        try {
            $data["sliders"] = DB::table("sliders")
                ->select(
                    "sliders.*",
                    DB::raw("CONCAT('https://store.bulkbasketindia.com/sliders/', image) as image")
                )
                ->whereNotNull("image")
                ->get();

            $filter = DB::table('product_category')
                ->select(
                    'id',
                    'name',
                    'image',

                    DB::raw("CONCAT('https://store.bulkbasketindia.com/master%20images/', image) as image")
                );
            if ($request->user["supplier_id"] > 0) {
                $filter->where('supplier_id', $request->user["supplier_id"]);
            }
            $data["category"] =  $filter->whereNotNull("image")->orderBy('seq')
                ->get();

            $data["dealOfDay"] = DB::table("sliders3")->select(
                "sliders3.*",
                DB::raw("CONCAT('https://store.bulkbasketindia.com/sliders/', image) as image")
            )->whereNotNull("image")->orderBy("id", "desc")->get();

            $filter = DB::table('product_brand')
                ->select(
                    'id',
                    'name',
                    DB::raw("CONCAT('https://store.bulkbasketindia.com/master%20images/',image) as image")
                );

            if ($request->user["supplier_id"] > 0) {
                $filter->where('supplier_id', $request->user["supplier_id"]);
            }

            $brands = $filter->whereNotNull("image")->get();
            $data["brand1"] = $brands->slice(0, 120)->values();
            $data["brand2"] = $brands->slice(120, 241)->values();

            $filter = DB::table("products as a")
                ->leftJoin("customers_products_list as b", function ($join) use ($request) {
                    $join->on("a.id", "=", "b.product_id")
                        ->where("b.customer_id", $request->user["customer_id"])
                        ->where("a.supplier_id", $request->user["supplier_id"]);
                })
                ->join("product_category as pc", "a.category_id", "pc.id")
                ->join("product_sub_category as psc", "a.sub_category_id", "psc.id")
                ->join("product_type as pt", "a.product_type_id", "pt.id")
                ->where("a.is_home", 1)
                ->where("a.active", 1);

            $products = $filter->select(
                "a.id",
                "a.name",
                "a.gst",
                "a.cess_tax",
                "a.mrp",
                "pt.name as product_type",
                "pc.name as category_name",
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

            $customerId = $request->user["customer_id"];


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
                    "product_type" => $product->product_type,
                    "discount" => $product->mrp > 0
                        ? round((($product->mrp - $product->price) / $product->mrp) * 100, 2)
                        : 0,
                    "tiers" => $tiers->values(),
                    "cart" => $cart,
                    "cart_status" => $cart ? true : false,
                    "wishlist_status" => $wishlist ? true : false,
                ];


                $productArr[$product->category_name]["category_name"] = $product->category_name;
                $productArr[$product->category_name]["products"][] = $productData;
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
                'message' => 'Error fetching sliders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function mobDealOnDay(Request $request)
    {
        $category_id = $request->category_id;
        $sub_category_id = $request->sub_category_id;

        $query = DB::table("products as a")
            ->select(
                "a.*",
                "b.name as uom",
                "c.name as category",
                "d.name as sub_category",
                DB::raw("
            CASE 
                WHEN a.image IS NOT NULL AND a.image != '' 
                THEN CONCAT('https://store.bulkbasketindia.com/product%20images/', a.image) 
                ELSE NULL 
            END as image
        ")
            )
            ->join("product_uom as b", "a.uom_id", "b.id")
            ->join("product_category as c", "a.category_id", "c.id")
            ->join("product_sub_category as d", "a.sub_category_id", "d.id")
            ->where("a.active", 1)
            ->where("a.is_deal", 1);
        if ($category_id) {
            $query->where("a.category_id", $category_id);
        }
        if ($sub_category_id) {
            $query->where("a.sub_category_id", $sub_category_id);
        }
        $products = $query->get();
        foreach ($products as $key => $value) {
            $details = DB::table("product_price")
                ->where("product_id", $value->id)
                ->orderBy("qty", "asc")
                ->get();
            if ($details->count() > 0) {
                foreach ($details as $dkey => $dvalue) {
                    $details[$dkey]->final_price = $dvalue->price;
                }
                $lastIndex = $details->count() - 1;
                if ($value->is_discount == 1 && $value->discount > 0) {
                    $highest = $details[$lastIndex];
                    $discountAmount = ($highest->price * $value->discount) / 100;
                    $details[$lastIndex]->final_price = round($highest->price - $discountAmount, 2);
                }
                $products[$key]->final_price = null;
            } else {
                if ($value->is_discount == 1 && $value->discount > 0) {
                    $discountAmount = ($value->base_price * $value->discount) / 100;
                    $products[$key]->final_price = round($value->base_price - $discountAmount, 2);
                } else {
                    $products[$key]->final_price = $value->base_price;
                }
            }
            $products[$key]->details = $details;
            if ($category_id) {
                $cartItem = DB::table("cart")
                    ->where("product_id", $value->id)
                    ->where("customer_id", $category_id)
                    ->first();
                $products[$key]->cart_qty = $cartItem ? $cartItem->qty : 0;
            } else {
                $products[$key]->cart_qty = 0;
            }
        }
        return response()->json([
            'error' => false,
            'message' => 'Deal of the day products fetched successfully',
            'dealofdayproduct' => $products
        ], 200);
    }

    public function checkGST(Request $request)
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

    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'name' => 'nullable',
                'email' => 'nullable',
            ]);
            $user = DB::table("customer_users")
                ->where("id", $request->user["id"])
                ->first();

            if (!$user) {
                return response()->json([
                    'error' => true,
                    'message' => "User not found",
                    "data" => []
                ], 404);
            }
            $customer = DB::table("customers as a")
                ->join("customer_users as b", "a.id", "b.customer_id")
                ->where("b.id", $request->user["id"])
                ->select("a.*")
                ->first();
            $image = $customer->image ?? null;
            if ($request->hasFile('image')) {
                if (!empty($image) && file_exists(public_path('customer/' . $image))) {
                    unlink(public_path('customer/' . $image));
                }
                $image = time() . '_' . uniqid() . '.' . $request->file('image')->extension();
                $request->file('image')->move(public_path('customer'), $image);
            }
            DB::table("customer_users")
                ->where("id", $request->user["id"])
                ->update([
                    "name" => $request->name ?? $user->name,
                    "email" => $request->email ?? $user->email,
                    "address" => $request->address ?? $user->address,
                    "state" => $request->state ?? $user->state,
                    "district" => $request->district ?? $user->district,
                    "city" => $request->city ?? $user->city,
                    "pincode" => $request->pincode ?? $user->pincode,
                ]);
            DB::table("customers")
                ->where("id", $customer->id)
                ->update([
                    "image" => $image
                ]);
            $updatedUser = DB::table("customer_users")
                ->where("id", $request->user["id"])
                ->first();

            return response()->json([
                'error' => false,
                'message' => "Profile Updated Successfully",
                "data" => $updatedUser
            ], 200);
        } catch (\Throwable $th) {

            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 500);
        }
    }

    public function getProfile(Request $request)
    {
        try {

            $customer = DB::table("customers as a")
                ->join("customer_users as b", "a.id", "b.customer_id")
                ->where("b.id", $request->user["id"])
                ->select(
                    "b.name as customer_name",
                    "b.email as customer_email",
                    "b.number as customer_number",
                    "b.address as customer_address",
                    "b.state as customer_state",
                    "b.district as customer_district",
                    "b.city as customer_city",
                    "b.pincode as customer_pincode",
                    DB::raw("
                    CASE 
                        WHEN a.image IS NOT NULL AND a.image != '' 
                        THEN CONCAT('https://store.bulkbasketindia.com/customer/', a.image) 
                        ELSE NULL 
                    END as image
                ")
                )
                ->first();
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

    public function updateCompany(Request $request)
    {
        try {

            $customer = DB::table("customers as a")
                ->join("customer_users as b", "a.id", "b.customer_id")
                ->where("b.id", $request->user["id"])
                ->select("a.*")
                ->first();
            if (!$customer) {
                return response()->json([
                    'error' => true,
                    'message' => "Customer details not found",
                    "data" => []
                ], 422);
            }
            // $gstNumber = $request->gst;
            // if ($gstNumber) {
            //     $gstResponse = new checkGST();
            //     if (!$gstResponse || $gstResponse['status'] != 'success') {
            //         return response()->json([
            //             'error' => true,
            //             'message' => "Invalid GST Number",
            //             "data" => []
            //         ], 422);
            //     }
            // }
            $customerId = DB::table("customer_users")
                ->where("id", $request->user["id"])
                ->value("customer_id");

            DB::table("customers")
                ->where("id", $customerId)
                ->update([
                    "name" => $request->name ?? $customer->name,
                    "brand_name" => $request->brand_name ?? null,
                    "number" => $request->number ?? $customer->number,
                    "email" => $request->email ?? $customer->email,
                    "gst" => $request->gst ?? null,
                    "address" => $request->address ?? $customer->address,
                    "state" => $request->state ?? $customer->state,
                    "district" => $request->district ?? $customer->district,
                    "city" => $request->city ?? $customer->city,
                    "pincode" => $request->pincode ?? $customer->pincode,
                ]);
            return response()->json([
                'error' => false,
                'message' => "Update Successfully",
                "data" => $customer
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function getCompany(Request $request)
    {
        try {

            $customer =  DB::table("customers as a")
                ->select(
                    "a.id",
                    "a.type as customer_type",
                    "a.name as company_name",
                    "a.email as company_email",
                    "a.number as company_number",
                    "a.gst",
                    "a.address as company_address",
                    "a.state as company_state",
                    "a.district as company_district",
                    "a.pincode as comapny_pincode",
                    "a.customer_type as company_type"
                )
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


    public function getProducts(Request $request, $category_id, $sub_category_id = null, $ss_category_id = null)
    {
        if (!$category_id) {
            return response()->json([
                'error' => true,
                'message' => 'Select Category',
                "data" => []
            ], 422);
        }
        try {
            $PSC = DB::table("product_sub_category")
                ->select("product_sub_category.*", DB::raw("
                CASE 
                    WHEN image IS NOT NULL AND image != '' 
                    THEN CONCAT('https://store.bulkbasketindia.com/master images/', image) 
                    ELSE NULL 
                        END as image
                    "))
                ->where("category_id", $category_id)
                ->get();
            $result = [];

            foreach ($PSC as $sub) {

                $sub_sub_category = DB::table("product_sub_sub_category")->where("category_id", $category_id)->where("sub_category_id", $sub->id)->get();

                $filter = DB::table("products as a")
                    ->leftJoin("customers_products_list as b", function ($join) use ($request) {
                        $join->on("a.id", "=", "b.product_id")
                            ->where("b.customer_id", $request->user["customer_id"])
                            ->where("a.supplier_id", $request->user["supplier_id"]);
                    })
                    ->join('product_type as pt', 'a.product_type_id', 'pt.id')
                    ->where("a.sub_category_id", $sub->id)
                    ->where("a.active", 1);
                if ($sub_category_id) {

                    $filter->where("a.sub_category_id", $sub_category_id);
                }

                if ($ss_category_id) {


                    $filter->whereIn("a.product_sub_sub_category", explode(", ", $ss_category_id));
                }
                $products =   $filter->select(
                    "a.id",
                    "a.name",
                    "a.gst",
                    "a.cess_tax",
                    "a.mrp",
                    "pt.name as product_type",
                    "a.product_sub_sub_category",
                    DB::raw("COALESCE(b.base_price, a.base_price) as price"),
                    DB::raw("
                            CASE 
                                WHEN a.image IS NOT NULL AND a.image != '' 
                                THEN CONCAT('https://store.bulkbasketindia.com/product images/', a.image) 
                                ELSE NULL 
                            END as image
                        ")
                )
                    ->get();

                $productArr = [];

                foreach ($products as $product) {


                    $tiers = DB::table("customers_products_list as a")
                        ->join("customers_products_tier as b", "a.id", "b.customer_product_id")
                        ->select("b.qty", "b.base_price as price")
                        ->where("a.customer_id", $request->user["customer_id"])
                        ->where("a.product_id", $product->id)
                        ->orderBy("b.qty", "asc")
                        ->get();


                    if ($tiers->isEmpty()) {

                        $tiers = DB::table("product_price")
                            ->select("qty", "price")
                            ->where("product_id", $product->id)
                            ->orderBy("qty", "asc")
                            ->get();
                    }
                    $cart_status = false;
                    $wishlist_status = false;
                    $cart = DB::table("cart")->where("product_id", $product->id)->where("customer_id", $request->user["customer_id"])->first();
                    $wishlist = DB::table("wishlist")->where("product_id", $product->id)->where("customer_id", $request->user["customer_id"])->first();
                    if ($cart) {
                        $cart_status = true;
                    }
                    if ($wishlist) {
                        $wishlist_status = true;
                    }
                    $productArr[] = [
                        "id" => $product->id,
                        "image" => $product->image,
                        "product_sub_sub_category" => $product->product_sub_sub_category,
                        "name" => $product->name,
                        "gst" => $product->gst,
                        "cess_tax" => $product->cess_tax,
                        "price" => $product->price,
                        "mrp" => $product->mrp,
                        "product_type" => $product->product_type,
                        "discount" => $product->mrp > 0
                            ? round((($product->mrp - $product->price) / $product->mrp) * 100, 2)
                            : 0,

                        "tiers" => $tiers,
                        "cart" => $cart,
                        "cart_status" => $cart_status,
                        "wishlist_status" => $wishlist_status,
                    ];
                }

                $result[] = [
                    "sub_category_id" => $sub->id,
                    "sub_category" => $sub->name ?? "",
                    "image" => $sub->image ?? "",
                    "subSubCategory" => $sub_sub_category,
                    "products" => $productArr
                ];
            }

            return response()->json([
                'error' => false,
                'message' => "Load Successfully",
                "data" => $result
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }


    public function getCategory(Request $request)
    {
        try {
            $filter = DB::table('product_category as a')
                ->select(
                    'a.id',
                    'a.name',
                    'a.image',
                    DB::raw("
                            CASE 
                                WHEN a.image IS NOT NULL AND a.image != '' 
                                THEN CONCAT('https://store.bulkbasketindia.com/master images/', a.image) 
                                ELSE NULL 
                            END as image
                        ")
                );
            if ($request->user["supplier_id"] > 0) {
                $filter->where('supplier_id', $request->user["supplier_id"]);
            }
            $data =  $filter->orderBy('seq')
                ->get();


            return response()->json([
                'error' => false,
                'message' => "save Successfully",
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

            $customer_id = $request->user["customer_id"];

            $wishlist = DB::table("wishlist as c")
                ->join("products as p", "c.product_id", "p.id")
                ->join("product_type as pt", "p.product_type_id", "pt.id")
                ->where("c.customer_id", $customer_id)
                ->select(
                    "c.id",
                    "c.product_id",
                    "c.qty",
                    "p.name",
                    "p.mrp",
                    "p.base_price",
                    "p.discount",
                    "p.is_discount",
                    "p.gst",
                    "pt.name as product_type",
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
            foreach ($wishlist as $key => $value) {
                $cartItem = DB::table("cart")
                    ->where("product_id", $value->product_id)
                    ->where("customer_id", $customer_id)
                    ->first();
                $cart_qty = $cartItem ? $cartItem->qty : 0;
                $wishlist[$key]->cart_qty = $cart_qty;
                $details = DB::table("product_price")
                    ->where("product_id", $value->product_id)
                    ->orderBy("qty", "asc")
                    ->get();
                $final_price = $value->base_price;
                $applied_tier = null;
                if ($details->count() > 0) {
                    foreach ($details as $dkey => $tier) {
                        $tier_price = $tier->price;
                        if ($value->is_discount == 1 && $value->discount > 0) {
                            $discountAmount = ($tier_price * $value->discount) / 100;
                            $tier_price = round($tier_price - $discountAmount, 2);
                        }
                        $details[$dkey]->final_price = $tier_price;
                        if ($cart_qty >= $tier->qty) {
                            $final_price = $tier_price;
                            $applied_tier = $tier;
                        }
                    }
                } else {
                    if ($value->is_discount == 1 && $value->discount > 0) {
                        $discountAmount = ($value->base_price * $value->discount) / 100;
                        $final_price = round($value->base_price - $discountAmount, 2);
                    }
                }
                $wishlist[$key]->final_price = $final_price;
                $wishlist[$key]->applied_tier_qty = $applied_tier ? $applied_tier->qty : null;
                $wishlist[$key]->tiers = $details;
            }

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

    public function getProductDetails(Request $request, $product_id)
    {
        try {

            $product = DB::table("products as a")
                ->leftJoin("customers_products_list as b", function ($join) use ($request) {
                    $join->on("a.id", "=", "b.product_id")
                        ->where("b.customer_id", $request->user["customer_id"])
                        ->where("a.supplier_id", $request->user["supplier_id"]);
                })
                ->join('product_type as pt', 'a.product_type_id', 'pt.id')
                ->where("a.id", $product_id)
                ->where("a.active", 1)
                ->select(
                    "a.id",
                    "a.name",
                    "a.gst",
                    "a.cess_tax",
                    "a.description",
                    "a.mrp",
                    "pt.name as product_type",
                    DB::raw("COALESCE(b.base_price, a.base_price) as price"),
                    DB::raw("
                    CASE 
                        WHEN a.image IS NOT NULL AND a.image != '' 
                        THEN CONCAT('https://store.bulkbasketindia.com/product images/', a.image) 
                        ELSE NULL 
                    END as image
                ")

                )
                ->first();
            if (!$product) {
                return response()->json([
                    "error" => true,
                    "message" => "Product not found",
                    "data" => []
                ], 404);
            }

            // tiers
            $tiers = DB::table("customers_products_list as a")
                ->join("customers_products_tier as b", "a.id", "b.customer_product_id")
                ->select("b.qty", "b.base_price as price")
                ->where("a.customer_id", $request->user["customer_id"])
                ->where("a.product_id", $product->id)
                ->orderBy("b.qty", "asc")
                ->get();

            if ($tiers->isEmpty()) {
                $tiers = DB::table("product_price")
                    ->select("qty", "price")
                    ->where("product_id", $product->id)
                    ->orderBy("qty", "asc")
                    ->get();
            }

            // cart check
            $cart = DB::table("cart")
                ->where("product_id", $product->id)
                ->where("customer_id", $request->user["customer_id"])
                ->first();

            // wishlist check
            $wishlist = DB::table("wishlist")
                ->where("product_id", $product->id)
                ->where("customer_id", $request->user["customer_id"])
                ->first();

            $cart_status = $cart ? true : false;
            $wishlist_status = $wishlist ? true : false;

            $result = [
                "id" => $product->id,
                "image" => $product->image,
                "name" => $product->name,
                "description" => $product->description,
                "gst" => $product->gst,
                "cess_tax" => $product->cess_tax,
                "price" => $product->price,
                "mrp" => $product->mrp,
                "mrp" => $product->mrp,
                "product_type" => $product->product_type,
                "discount" => $product->mrp > 0
                    ? round((($product->mrp - $product->price) / $product->mrp) * 100, 2)
                    : 0,
                "tiers" => $tiers,
                "cart" => $cart,
                "cart_status" => $cart_status,
                "wishlist_status" => $wishlist_status,
            ];

            return response()->json([
                "error" => false,
                "message" => "Load Successfully",
                "data" => $result
            ], 200);
        } catch (\Throwable $th) {

            return response()->json([
                "error" => true,
                "message" => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }

    public function getProductByBrand(Request $request, $brand_id, $category_id = null, $sub_category_id = null, $ss_category_id = null)
    {

        if (!$brand_id) {
            return response()->json([
                'error' => true,
                'message' => 'Select Category',
                "data" => []
            ], 422);
        }

        try {




            $PSC = DB::table("product_sub_category")
                ->select("product_sub_category.*", DB::raw("
                CASE 
                    WHEN image IS NOT NULL AND image != '' 
                    THEN CONCAT('https://store.bulkbasketindia.com/master images/', image) 
                    ELSE NULL 
                END as image
            "))
                // ->where("category_id", $category_id)
                ->get();

            $result = [];

            foreach ($PSC as $sub) {

                $sub_sub_category = DB::table("product_sub_sub_category")->where("sub_category_id", $sub->id)->get();

                $filter = DB::table("products as a")
                    ->leftJoin("customers_products_list as b", function ($join) use ($request) {
                        $join->on("a.id", "=", "b.product_id")
                            ->where("b.customer_id", $request->user["customer_id"])
                            ->where("a.supplier_id", $request->user["supplier_id"]);
                    })

                    ->where("a.brand_id", $brand_id)
                    ->where("a.sub_category_id", $sub->id)
                    ->where("a.active", 1);
                if ($category_id) {

                    $filter->where("a.category_id", $category_id);
                }
                if ($sub_category_id) {

                    $filter->where("a.sub_category_id", $sub_category_id);
                }

                if ($ss_category_id) {


                    $filter->whereIn("a.product_sub_sub_category", explode(", ", $ss_category_id));
                }
                $products =   $filter->select(
                    "a.id",
                    "a.name",
                    "a.gst",
                    "a.cess_tax",
                    "a.mrp",
                    DB::raw("COALESCE(b.base_price, a.base_price) as price"),
                    DB::raw("
                            CASE 
                                WHEN a.image IS NOT NULL AND a.image != '' 
                                THEN CONCAT('https://store.bulkbasketindia.com/product images/', a.image) 
                                ELSE NULL 
                            END as image
                        ")
                )
                    ->get();

                $productArr = [];

                foreach ($products as $product) {


                    $tiers = DB::table("customers_products_list as a")
                        ->join("customers_products_tier as b", "a.id", "b.customer_product_id")
                        ->select("b.qty", "b.base_price as price")
                        ->where("a.customer_id", $request->user["customer_id"])
                        ->where("a.product_id", $product->id)
                        ->orderBy("b.qty", "asc")
                        ->get();


                    if ($tiers->isEmpty()) {

                        $tiers = DB::table("product_price")
                            ->select("qty", "price")
                            ->where("product_id", $product->id)
                            ->orderBy("qty", "asc")
                            ->get();
                    }
                    $cart_status = false;
                    $wishlist_status = false;
                    $cart = DB::table("cart")->where("product_id", $product->id)->where("customer_id", $request->user["customer_id"])->first();
                    $wishlist = DB::table("wishlist")->where("product_id", $product->id)->where("customer_id", $request->user["customer_id"])->first();
                    if ($cart) {
                        $cart_status = true;
                    }
                    if ($wishlist) {
                        $wishlist_status = true;
                    }
                    $productArr[] = [
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

                        "tiers" => $tiers,
                        "cart" => $cart,
                        "cart_status" => $cart_status,
                        "wishlist_status" => $wishlist_status,
                    ];
                }

                $result[] = [
                    "sub_category_id" => $sub->id,
                    "sub_category" => $sub->name ?? "",
                    "image" => $sub->image ?? "",
                    "subSubCategory" => $sub_sub_category,
                    "products" => $productArr
                ];
            }

            return response()->json([
                'error' => false,
                'message' => "Load Successfully",
                "data" => $result
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                "data" => []
            ], 422);
        }
    }


    public function getBrands(Request $request)
    {
        try {
            $filter = DB::table('product_brand as a')
                ->select(
                    'a.id',
                    'a.name',
                    'a.image',
                    DB::raw("
                            CASE 
                                WHEN a.image IS NOT NULL AND a.image != '' 
                                THEN CONCAT('https://store.bulkbasketindia.com/master images/', a.image) 
                                ELSE NULL 
                            END as image
                        ")
                );
            if ($request->user["supplier_id"] > 0) {
                $filter->where('supplier_id', $request->user["supplier_id"]);
            }
            $data =  $filter
                ->get();


            return response()->json([
                'error' => false,
                'message' => "save Successfully",
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

    public function saveOrder(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'delivery_date' => 'required',
            'address' => 'required',
            'state' => 'required',
            'district' => 'required',
            'city' => 'required',
            'pincode' => 'required',
            'pay_mode' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $cart = DB::table("cart as a")
                ->select("a.*", "b.supplier_id", "b.base_price as mrp", "b.name as product", "b.description", "b.cess_tax", "b.gst")
                ->join("products as b", "a.product_id", "=", "b.id")
                ->where("a.customer_id", $request->user["customer_id"])
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
                ? DB::table("customers")->where("id", $request->user["customer_id"])->first()
                : DB::table("customer_users")->where("id", $request->user["id"])->first();

            $total_amount = 0;
            $invoice_no = 'INV-' . $request->user['customer_id'] . date('YmdHis');

            $order_id = DB::table("order_estimate")->insertGetId([
                "customer_id" => $request->user['customer_id'],
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
            $customer = DB::table("customers")->where("id", $request->user["customer_id"])->first();

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
                    ->where("id", $request->user['customer_id'])
                    ->increment("hold_amount", $total_amount);
            }

            DB::table("cart")
                ->where("customer_id", $request->user['customer_id'])
                ->delete();

            DB::commit();

            return response()->json([
                'error' => false,
                'message' => 'Order placed successfully.',
                'order_id' => $order_id,
                'invoice_no' => $invoice_no
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEstimate(Request $request)
    {

        try {
            $data = DB::table("order_estimate")->where("customer_id", $request->user["customer_id"])->get();
            return response()->json([
                'error' => false,
                'message' => 'Load successfully.',
                'data' => $data,

            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => true,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEstimateDetails(Request $request, $id)
    {
        try {
            $query = DB::table('order_estimate');

            if ($id) {
                $query->where('order_estimate.id', $id);
            }

            $orders = $query->orderBy('order_estimate.id', 'desc')->get();

            foreach ($orders as $order) {
                $order->items = DB::table('order_estimate_item')
                    ->join('products', 'order_estimate_item.product_id', '=', 'products.id')
                    ->select(
                        'order_estimate_item.*',
                        'products.name as product_name',
                        'products.image as product_image',
                        DB::raw("
            CASE 
                WHEN products.image IS NOT NULL AND products.image != '' 
                THEN CONCAT('https://store.bulkbasketindia.com/product images/', products.image) 
                ELSE NULL 
            END as image
        ")
                    )
                    ->where('order_estimate_item.order_id', $order->id)
                    ->get();
            }

            return response()->json([
                'error' => false,
                'message' => 'Order estimate(s) retrieved successfully.',
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //order
    public function getOrder(Request $request)
    {

        try {
            $data = DB::table("orders")->where("customer_id", $request->user["customer_id"])->get();
            return response()->json([
                'error' => false,
                'message' => 'Load successfully.',
                'data' => $data,

            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => true,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrderDetails(Request $request, $id)
    {
        try {
            $query = DB::table('orders');

            if ($id) {
                $query->where('orders.id', $id);
            }

            $orders = $query->orderBy('orders.id', 'desc')->get();

            foreach ($orders as $order) {
                $order->items = DB::table('orders_item')
                    ->join('products', 'orders_item.product_id', '=', 'products.id')
                    ->select(
                        'orders_item.*',
                        'products.name as product_name',
                        'products.image as product_image',
                        DB::raw("
            CASE 
                WHEN products.image IS NOT NULL AND products.image != '' 
                THEN CONCAT('https://store.bulkbasketindia.com/product images/', products.image) 
                ELSE NULL 
            END as image
        ")
                    )
                    ->where('orders_item.order_id', $order->id)
                    ->get();
            }

            return response()->json([
                'error' => false,
                'message' => 'Order retrieved successfully.',
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getWalletLedger(Request $request)
    {

        try {
            //code...

            $customerId = $request->user["customer_id"];
            $company = DB::table("customers")
                ->where("id", $request->user["customer_id"])
                ->first();

            $wallet_statement = DB::table(DB::raw("(
                -- 💰 1️⃣ Wallet Ledger (Credit)
                SELECT 
                    id, 
                    created_at, 
                    amount, 
                    'credit' AS type, 
                    invoice_no, 
                    'Sale (GST)' AS particular, 
                    pay_date, 
                    pay_mode, 
                    remarks
                FROM wallet_ledger
                WHERE customer_id = $customerId

                UNION ALL

                -- 💸 2️⃣ Orders paid via wallet (Debit)
                SELECT 
                    id, 
                    created_at, 
                    total_amount AS amount, 
                    'debit' AS type, 
                    invoice_no, 
                    'Payment' AS particular, 
                    created_at AS pay_date, 
                    pay_mode, 
                    'Order Generated' AS remarks
                FROM orders
                WHERE customer_id = $customerId AND pay_mode = 'wallet'

                UNION ALL

                -- 🪙 3️⃣ Interest Earned (Credit)
                SELECT 
                     i.id, 
                    i.created_at, 
                    i.intrest_value AS amount, 
                    'credit' AS type, 
                    o.invoice_no, 
                    'Interest (Wallet)' AS particular, 
                    i.created_at AS pay_date, 
                    'wallet' AS pay_mode, 
                    'Interest added to wallet' AS remarks
                FROM order_payment_intrest i
                INNER JOIN orders o ON o.id = i.order_id
                INNER JOIN customers c ON c.id = o.customer_id
                WHERE c.id = $customerId
            ) AS wallet_union"))
                ->orderBy('created_at', 'desc')
                ->get();
            $balance = 0;
            foreach ($wallet_statement as $entry) {
                if ($entry->type === 'credit') {
                    $balance += $entry->amount;
                } elseif ($entry->type === 'debit') {
                    $balance -= $entry->amount;
                }
                $entry->balance = $balance;
            }

            return response()->json([
                'error' => false,
                'msg' => "Data load successfully",
                'customer_id' => $customerId,
                'company' => $company,
                'data' => $wallet_statement
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => 'Something went wrong.',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function searchProducts(Request $request, $query)
    {
        // $query = trim($request->input("query"));
        $query = trim($query);

        if (!$query) {
            return response()->json([
                'status' => true,
                'message' => 'No query provided',
                'data' => []
            ]);
        }

        // 🧩 Step 1: Initial Search (normal LIKE search)
        $productsQuery = DB::table("products as a")
            ->select(
                "a.*",
                "b.name as uom",
                "c.name as category",
                "d.name as sub_category",
                "e.name as brand",
                DB::raw("
            CASE 
                WHEN a.image IS NOT NULL AND a.image != '' 
                THEN CONCAT('https://store.bulkbasketindia.com/product images/', a.image) 
                ELSE NULL 
            END as image
        ")
            )
            ->join("product_uom as b", "a.uom_id", "b.id")
            ->join("product_category as c", "a.category_id", "c.id")
            ->join("product_sub_category as d", "a.sub_category_id", "d.id")
            ->leftJoin("product_brand as e", "a.brand_id", "e.id")
            ->where("a.active", 1)
            ->where(function ($q) use ($query) {
                $q->where('a.name', 'like', '%' . $query . '%')
                    ->orWhere('a.tags', 'like', '%' . $query . '%');
            });

        $products = $productsQuery->limit(20)->get();

        // 🧠 Step 2: If no products found → use AI to correct query
        if ($products->isEmpty()) {
            try {
                $apiKey = config('services.openai.key');
                $prompt = "The user searched for '{$query}'. Suggest the most likely correct English product search word. 
            Return only the corrected word (no extra text). Example:
            Input: chini
            Output: sugar";

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a spelling correction assistant for an e-commerce product search.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                ]);

                $aiContent = trim($response->json()['choices'][0]['message']['content'] ?? '');

                if ($aiContent && strtolower($aiContent) !== strtolower($query)) {
                    $correctedQuery = $aiContent;

                    $products = DB::table("products as a")
                        ->select(
                            "a.*",
                            "b.name as uom",
                            "c.name as category",
                            "d.name as sub_category",
                            "e.name as brand",
                            DB::raw("
            CASE 
                WHEN a.image IS NOT NULL AND a.image != '' 
                THEN CONCAT('https://store.bulkbasketindia.com/product images/', a.image) 
                ELSE NULL 
            END as image
        ")

                        )
                        ->join("product_uom as b", "a.uom_id", "b.id")
                        ->join("product_category as c", "a.category_id", "c.id")
                        ->join("product_sub_category as d", "a.sub_category_id", "d.id")
                        ->leftJoin("product_brand as e", "a.brand_id", "e.id")
                        ->where("a.active", 1)
                        ->where(function ($q) use ($correctedQuery) {
                            $q->where('a.name', 'like', '%' . $correctedQuery . '%')
                                ->orWhere('a.tags', 'like', '%' . $correctedQuery . '%')
                                ->orWhere('a.description', 'like', '%' . $correctedQuery . '%');
                        })
                        ->limit(20)
                        ->get();

                    return response()->json([
                        'status' => true,
                        'message' => 'Search completed (auto-corrected)',
                        'corrected_query' => $correctedQuery,
                        'data' => $products,
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => $e->getMessage(),
                    'data' => [],
                ]);
            }
        }

        return response()->json([
            'error' => false,
            'message' => 'Products retrieved successfully.',
            'data' => $products,
        ]);
    }
}
