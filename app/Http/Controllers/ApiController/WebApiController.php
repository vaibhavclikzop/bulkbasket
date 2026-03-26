<?php

namespace App\Http\Controllers\ApiController;

use App\Http\Controllers\Controller;
use App\Services\checkGST;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

    public function getCategory(Request $request)
    {
        $categories = DB::table('product_category')
            ->select('id', 'name', 'image')
            ->where('supplier_id', 1)
            ->orderBy('seq')
            ->get();
        return response()->json([
            'status' => true,
            'message' => 'Category list retrieved successfully.',
            'data' => $categories
        ]);
    }

    public function getSubCategory(Request $request)
    {
        $categories = DB::table('product_sub_category')
            ->select('id', 'category_id', 'name', 'image')
            ->where('supplier_id', 1)
            ->get();
        return response()->json([
            'status' => true,
            'message' => 'Sub Category list Retrieved Successfully.',
            'data' => $categories
        ]);
    }

    public function getSubSubCategory(Request $request)
    {
        $subcategories = DB::table('product_sub_sub_category')
            ->select('id', 'category_id', 'sub_category_id', 'name', 'image')
            ->where('supplier_id', 1)
            ->get();
        return response()->json([
            'status' => true,
            'message' => 'Sub Sub Category list Retrieved Successfully.',
            'data' => $subcategories
        ]);
    }

    public function getBrands(Request $request)
    {
        $brands = DB::table('product_brand')
            ->select('id', 'name', 'image')
            ->where('supplier_id', 1)
            ->get();
        return response()->json([
            'status' => true,
            'message' => 'Brands list retrieved successfully.',
            'data' => $brands
        ]);
    }

    public function getBrandSubcategory(Request $request, $id, $subcategoryId = null)
    {
        $query = DB::table('products as a')
            ->select(
                'a.*',
                'b.name as uom',
                'c.name as category',
                'd.id as sub_category_id',
                'd.name as sub_category'
            )
            ->join('product_uom as b', 'a.uom_id', '=', 'b.id')
            ->join('product_category as c', 'a.category_id', '=', 'c.id')
            ->join('product_sub_category as d', 'a.sub_category_id', '=', 'd.id')
            ->where('a.active', 1)
            ->where('a.brand_id', $id);

        if ($subcategoryId) {
            $query->where('a.sub_category_id', $subcategoryId);
        }

        $products = $query->get();

        $subcategories = $products->map(function ($p) {
            return [
                'id' => $p->sub_category_id,
                'name' => $p->sub_category,
            ];
        })->unique('id')->values();
        return response()->json([
            'products' => $products,
            'subcategories' => $subcategories
        ]);
    }

    public function getProducts(Request $request)
    {
        $query = trim($request->input("query"));

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
                "e.name as brand"
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
                            "e.name as brand"
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
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully.',
            'data' => $products,
        ]);
    }


    public function getAllProducts()
    {
        $category_id = request("category_id");
        $sub_category_id = request("sub_category_id");
        $sub_sub_category_id = request("sub_sub_category_id");
        $brand_id = request("brand_id");
        $query = request("search");

        $customer_id = "";

        $categories = DB::table("product_category")->get();

        $subCategories = DB::table("product_sub_category")
            ->where("category_id", $category_id)
            ->get();
        $prod = DB::table("products as a")
            ->select(
                "a.*",
                "b.name as uom",
                "c.name as category",
                "d.name as sub_category",
                "e.name as sub_sub_category"
            )
            ->join("product_uom as b", "a.uom_id", "b.id")
            ->join("product_category as c", "a.category_id", "c.id")
            ->join("product_sub_category as d", "a.sub_category_id", "d.id")
            ->leftJoin("product_sub_sub_category as e", "a.product_sub_sub_category", "e.id")
            ->where("a.active", 1);
        if ($category_id) {
            $prod->where("a.category_id", $category_id);
        }
        if ($sub_category_id) {
            $prod->where("a.sub_category_id", $sub_category_id);
        }
        if ($sub_sub_category_id) {
            $subSubIds = explode(',', $sub_sub_category_id);
            $prod->whereIn("a.product_sub_sub_category", $subSubIds);
        }
        if ($brand_id) {
            $prod->where("a.brand_id", $brand_id);
        }
        if ($query) {
            $prod->where(function ($q) use ($query) {
                $q->where('a.name', 'like', '%' . $query . '%')
                    ->orWhere('a.description', 'like', '%' . $query . '%')
                    ->orWhere('c.name', 'like', '%' . $query . '%')
                    ->orWhere('d.name', 'like', '%' . $query . '%')
                    ->orWhere('e.name', 'like', '%' . $query . '%')
                    ->orWhere('a.tags', 'like', '%' . $query . '%');
            });
        }
        $products = $prod->paginate(250);
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
            if ($customer_id) {
                $cartItem = DB::table("cart")
                    ->where("product_id", $value->id)
                    ->where("customer_id", $customer_id)
                    ->first();
                $products[$key]->cart_qty = $cartItem ? $cartItem->qty : 0;
            } else {
                $products[$key]->cart_qty = 0;
            }
        }
        return $products;
    }

    public function dealOnDay(Request $request)
    {
        $category_id = $request->category_id;
        $sub_category_id = $request->sub_category_id;

        $query = DB::table("products as a")
            ->select("a.*", "b.name as uom", "c.name as category", "d.name as sub_category")
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
        return $products;
    }

    public function SlidersApi(Request $request)
    {
        try {
            $data = DB::table("sliders")->get();

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

    public function getproduct(Request $request)
    {
        $customer_id = $request->user['customer_id'] ?? null;

        if (!$customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Customer ID not found in request.',
            ], 401);
        }

        // Get wishlist product IDs
        $wishlistIds = DB::table('wishlist')
            ->where('customer_id', $customer_id)
            ->pluck('product_id');

        if ($wishlistIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        // Fetch products in wishlist
        $products = DB::table('products as a')
            ->select('a.*', 'b.name as uom', 'c.name as category', 'd.name as sub_category')
            ->join('product_uom as b', 'a.uom_id', 'b.id')
            ->join('product_category as c', 'a.category_id', 'c.id')
            ->join('product_sub_category as d', 'a.sub_category_id', 'd.id')
            ->whereIn('a.id', $wishlistIds)
            ->where('a.active', 1)
            ->get();

        // Add details and cart quantity
        foreach ($products as $key => $product) {
            $products[$key]->details = DB::table('product_price')
                ->where('product_id', $product->id)
                ->get();

            $cartItem = DB::table('cart')
                ->where('product_id', $product->id)
                ->where('customer_id', $customer_id)
                ->first();

            $products[$key]->cart_qty = $cartItem ? $cartItem->qty : 0;
        }

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function ProductDetailsApi(Request $request, $id)
    {
        $product = DB::table("products as a")
            ->select("a.*","a.base_price as final_price", "b.name as uom", "c.name as category", "d.name as sub_category")
            ->join("product_uom as b", "a.uom_id", "b.id")
            ->join("product_category as c", "a.category_id", "c.id")
            ->join("product_sub_category as d", "a.sub_category_id", "d.id")
            ->where("a.id", $id)
            ->where("a.active", 1)
            ->first();

        if (!$product) {
            return response()->json([
                "status" => false,
                "message" => "Product not found"
            ], 404);
        }

        $product->tier = DB::table("product_price as a")->select('a.product_id','a.qty','a.price as final_price')
        ->where("product_id", $product->id)->get();
        $web_token = $request->header('web_token') ?? session('web_token');

        if ($web_token) {
            $customer = DB::table("customer_users")->where("web_token", $web_token)->first();
            if ($customer) {
                $cart = DB::table("cart")
                    ->where("customer_id", $customer->customer_id)
                    ->where("product_id", $id)
                    ->first();

                if ($cart) {
                    foreach ($product->details as $tier) {
                        if ($cart->qty >= $tier->qty) {
                            $product->mrp = $tier->price;
                        }
                    }
                }
            }
        }

        $supplier = DB::table("suppliers")->where("id", $product->supplier_id)->first();

        $related_products = DB::table("products as a")
            ->select("a.*", "b.name as uom", "c.name as category", "d.name as sub_category")
            ->join("product_uom as b", "a.uom_id", "b.id")
            ->join("product_category as c", "a.category_id", "c.id")
            ->join("product_sub_category as d", "a.sub_category_id", "d.id")
            ->where("a.sub_category_id", $product->sub_category_id)
            ->where("a.active", 1)
            ->get();

        $images = DB::table("product_images")->where("product_id", $id)->get();

        return response()->json([
            "status" => true,
            "message" => "Product details fetched successfully",
            "data" => [
                "product" => $product,
                "supplier" => $supplier,
                "related_products" => $related_products,
                "images" => $images
            ]
        ], 200);
    }
    
     public function getProductDetails(Request $request, $product_id)
    {
        try {

            $product = DB::table("products as a")
                ->leftJoin("customers_products_list as b", function ($join) use ($request) {
                    $join->on("a.id", "=", "b.product_id")
                        ->where("b.customer_id", $request->user["customer_id"])
                        ->where("a.supplier_id", 1);
                })
                ->where("a.id", $product_id)
                ->where("a.active", 1)
                ->select(
                    "a.id",
                    "a.name",
                    "a.gst",
                    "a.cess_tax",
                    "a.description",
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

    public function dealofDayApi(Request $request)
    {
        try {
            $data = DB::table("sliders3")->orderBy("id", "desc")->get();

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

    public function brandSliderApi(Request $request)
    {
        try {
            $data = DB::table("sliders4")->orderBy("id", "desc")->get();

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

    public function FooterBannerApi(Request $request)
    {
        try {
            $data = DB::table("sliders2")->orderBy("id", "desc")->get();

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

    public function faqCategory(Request $request)
    {
        $faq_category = DB::table("faq_category")->orderBy("seq")->get();
        $data = DB::table("main_faq as f")
            ->join("faq_category as c", "f.faq_cat_id", "=", "c.id")
            ->select("f.*", "c.name as category_name")
            ->get();
        return response()->json([
            'success' => true,
            'faq_category' => $faq_category,
            'faqs' => $data
        ]);
    }

    public function qulityMainList(Request $request)
    {
        try {
            $data = DB::table("quality_step")->get();
            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function getLocation(Request $request)
    {
        try {

            // ✅ LOGIN USER LOCATION (FROM DB)
            if ($request->customer_id) {

                $customer = DB::table('customers')
                    ->where('id', $request->customer_id)
                    ->first();

                if ($customer) {

                    $locationParts = array_filter([
                        $customer->address,
                        $customer->city,
                        $customer->district,
                        $customer->state,
                        $customer->pincode,
                    ]);

                    return response()->json([
                        'user_location' => implode(', ', $locationParts),
                        'source' => 'customer_table',
                    ]);
                }
            }

            // ✅ GUEST USER → GOOGLE MAP LOCATION
            $lat = $request->lat;
            $lng = $request->lng;

            if (!$lat || !$lng) {
                return response()->json([
                    'error' => 'Latitude and longitude required'
                ], 400);
            }

            $apiKey = config('services.google.map_key');

            $response = Http::get(
                'https://maps.googleapis.com/maps/api/geocode/json',
                [
                    'latlng' => $lat . ',' . $lng,
                    'key' => $apiKey,
                ]
            );

            $data = $response->json();

            if (($data['status'] ?? '') !== 'OK') {
                return response()->json([
                    'error' => 'Google location failed',
                    'google_status' => $data['status'] ?? null
                ], 400);
            }

            return response()->json([
                'user_location' => $data['results'][0]['formatted_address'] ?? null,
                'source' => 'google_map',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Location fetch failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function refund()
    {
        $refund = DB::table('pages')->where('id', 1)->first();

        if (!$refund) {
            return response()->json([
                'status' => false,
                'message' => 'Refund policy not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $refund
        ]);
    }

    public function terms()
    {
        $term = DB::table('pages')->where('id', 2)->first();

        if (!$term) {
            return response()->json([
                'status' => false,
                'message' => 'Terms & Conditions not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $term
        ]);
    }

    public function privacy()
    {
        $privacy = DB::table('pages')->where('id', 3)->first();

        if (!$privacy) {
            return response()->json([
                'status' => false,
                'message' => 'Privacy policy not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $privacy
        ]);
    }

    public function orderDelivery()
    {
        $privacy = DB::table('pages')->where('id', 5)->first();

        if (!$privacy) {
            return response()->json([
                'status' => false,
                'message' => 'Privacy policy not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $privacy
        ]);
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
            $user = DB::table("customer_users")
                ->where("id", $request->user["id"])
                ->first();
            if (!$user) {
                return response()->json([
                    'error' => true,
                    'message' => "User not found",
                    "data" => []
                ], 422);
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
            ], 422);
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
            $gstNumber = $request->gst;
            if ($gstNumber) {
                $gstResponse = new checkGST();
                if (!$gstResponse || $gstResponse['status'] != 'success') {
                    return response()->json([
                        'error' => true,
                        'message' => "Invalid GST Number",
                        "data" => []
                    ], 422);
                }
            }
            DB::table("customers as a")
                ->join("customer_users as b", "a.id", "b.customer_id")
                ->where("b.id", $request->user["id"])
                ->update([
                    "a.name" => $request->name ?? $customer->name,
                    "a.number" => $request->number ?? $customer->number,
                    "a.email" => $request->email ?? $customer->email,
                    "a.gst" => $gstNumber ?? $customer->gst,
                    "a.address" => $request->address ?? $customer->address,
                    "a.state" => $request->state ?? $customer->state,
                    "a.district" => $request->district ?? $customer->district,
                    "a.city" => $request->city ?? $customer->city,
                    "a.pincode" => $request->pincode ?? $customer->pincode,
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
