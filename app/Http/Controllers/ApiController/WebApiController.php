<?php

namespace App\Http\Controllers\ApiController;

use App\Http\Controllers\Controller;
use App\Services\checkGST;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

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
        $productsQuery = DB::table("products as a")
            ->select(
                "a.*",
                "b.name as uom",
                "c.name as category",
                "d.name as sub_category",
                "e.name as brand",
                "pt.name as product_type",
                DB::raw("
                        (SELECT COALESCE(SUM(cs.stock), 0) 
                        FROM current_stock cs 
                        WHERE cs.product_id = a.id
                        ) as current_stock
                ")
            )
            ->join("product_uom as b", "a.uom_id", "b.id")
            ->join("product_category as c", "a.category_id", "c.id")
            ->join("product_sub_category as d", "a.sub_category_id", "d.id")
            ->leftJoin("product_type as pt", "a.product_type_id", "pt.id")
            ->leftJoin("product_brand as e", "a.brand_id", "e.id")
            ->where("a.active", 1)
            ->where(function ($q) use ($query) {
                $q->where('a.name', 'like', '%' . $query . '%')
                    ->orWhere('a.tags', 'like', '%' . $query . '%');
            });

        $products = $productsQuery->limit(20)->get();
        foreach ($products as $key => $product) {

            $tiers = DB::table("product_price")
                ->where("product_id", $product->id)
                ->orderBy("qty", "asc")
                ->get();
            foreach ($tiers as $tkey => $tier) {
                $tiers[$tkey]->final_price = $tier->price;
            }

            $products[$key]->tiers = $tiers;
        }
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
                            "pt.name as product_type",
                            DB::raw("
                                (SELECT COALESCE(SUM(cs.stock), 0) 
                                FROM current_stock cs 
                                WHERE cs.product_id = a.id
                                ) as current_stock
                            ")
                        )
                        ->join("product_uom as b", "a.uom_id", "b.id")
                        ->join("product_category as c", "a.category_id", "c.id")
                        ->join("product_sub_category as d", "a.sub_category_id", "d.id")
                        ->leftJoin("product_type as pt", "a.product_type_id", "pt.id")
                        ->leftJoin("product_brand as e", "a.brand_id", "e.id")
                        ->where("a.active", 1)
                        ->where(function ($q) use ($correctedQuery) {
                            $q->where('a.name', 'like', '%' . $correctedQuery . '%')
                                ->orWhere('a.tags', 'like', '%' . $correctedQuery . '%')
                                ->orWhere('a.description', 'like', '%' . $correctedQuery . '%');
                        })
                        ->limit(20)
                        ->get();
                    foreach ($products as $key => $product) {

                        $tiers = DB::table("product_price")
                            ->where("product_id", $product->id)
                            ->orderBy("qty", "asc")
                            ->get();

                        foreach ($tiers as $tkey => $tier) {
                            $tiers[$tkey]->final_price = $tier->price;
                        }

                        $products[$key]->tiers = $tiers;
                    }
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
                "a.image",
                "b.name as uom",
                "c.name as category",
                "d.name as sub_category",
                "e.name as sub_sub_category",
                "f.name as product_type",
                DB::raw("
                        (SELECT COALESCE(SUM(cs.stock), 0) 
                        FROM current_stock cs 
                        WHERE cs.product_id = a.id
                        ) as current_stock
                    ")
            )
            ->join("product_uom as b", "a.uom_id", "b.id")
            ->join("product_category as c", "a.category_id", "c.id")
            ->join("product_sub_category as d", "a.sub_category_id", "d.id")
            ->leftJoin("product_type as f", "a.product_type_id", "f.id")
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
            $final_price = null;
            if ($details->count() > 0) {

                foreach ($details as $dkey => $dvalue) {
                    $details[$dkey]->final_price = $dvalue->price;
                }
                $lastIndex = $details->count() - 1;
                $highest = $details[$lastIndex];
                if ($value->is_discount == 1 && $value->discount > 0) {
                    $discountAmount = ($highest->price * $value->discount) / 100;
                    $final_price = round($highest->price - $discountAmount, 2);

                    $details[$lastIndex]->final_price = $final_price;
                } else {
                    $final_price = $highest->price;
                }
            } else {

                if ($value->is_discount == 1 && $value->discount > 0) {
                    $discountAmount = ($value->base_price * $value->discount) / 100;
                    $final_price = round($value->base_price - $discountAmount, 2);
                } else {
                    $final_price = $value->base_price;
                }
            }
            $products[$key]->final_price = $final_price;
            $price_for_discount = $final_price ?? $value->base_price;

            $discount = null;
            if ($value->mrp > 0) {
                $discount = round((($value->mrp - $price_for_discount) / $value->mrp) * 100, 2);
            }

            $products[$key]->discount = $discount;
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
            ->select(
                "a.*",
                "b.name as uom",
                "c.name as category",
                "d.name as sub_category",
                "f.name as product_type",
                "bd.name as brand_name",
                DB::raw("(
                SELECT COALESCE(SUM(cs.stock), 0)
                FROM current_stock cs
                WHERE cs.product_id = a.id
            ) as current_stock")
            )
            ->join("product_uom as b", "a.uom_id", "b.id")
            ->join("product_category as c", "a.category_id", "c.id")
            ->leftJoin("product_sub_category as d", "a.sub_category_id", "d.id")
            ->leftJoin("product_type as f", "a.product_type_id", "f.id")
            ->leftJoin("product_brand as bd", "a.brand_id", "bd.id")
            ->where("a.id", $id)
            ->where("a.active", 1)
            ->first();

        if (!$product) {
            return response()->json([
                "status" => false,
                "message" => "Product not found"
            ], 404);
        }

        if (isset($product->temp_image)) {
            unset($product->temp_image);
        }
        $details = DB::table("product_price")
            ->where("product_id", $product->id)
            ->orderBy("qty", "asc")
            ->get();
        $product->details = $details;
        $web_token = $request->header('web_token') ?? session('web_token');
        $final_price = $product->base_price;
        if ($details->count() > 0) {
            $final_price = $details->first()->price;

            foreach ($details as $tier) {
                if ($tier->price) {
                    $final_price = $tier->price;
                }
            }
            if ($product->is_discount == 1 && $product->discount > 0) {
                $discountAmount = ($final_price * $product->discount) / 100;
                $final_price = round($final_price - $discountAmount, 2);
            }
        } else {
            if ($product->is_discount == 1 && $product->discount > 0) {
                $discountAmount = ($product->base_price * $product->discount) / 100;
                $final_price = round($product->base_price - $discountAmount, 2);
            }
        }
        $product->final_price = $final_price;
        $price_for_discount = $final_price ?? $product->base_price;

        $discount = null;
        if ($product->mrp > 0) {
            $discount = round((($product->mrp - $price_for_discount) / $product->mrp) * 100, 2);
        }
        $product->discount = $discount;
        $product->cart_qty = 0;

        if ($web_token) {
            $customer = DB::table("customer_users")
                ->where("web_token", $web_token)
                ->first();

            if ($customer) {
                $cart = DB::table("cart")
                    ->where("customer_id", $customer->customer_id)
                    ->where("product_id", $id)
                    ->first();

                if ($cart) {
                    $product->cart_qty = $cart->qty;
                }
            }
        }
        $supplier = DB::table("suppliers")->where("id", $product->supplier_id)->first();
        $images = DB::table("product_images")->where("product_id", $id)->get();

        $related_products = DB::table("products as a")
            ->select(
                "a.*",
                "b.name as uom",
                "c.name as category",
                "d.name as sub_category",
                DB::raw("
                        (SELECT COALESCE(SUM(cs.stock), 0) 
                        FROM current_stock cs 
                        WHERE cs.product_id = a.id
                        ) as current_stock
                    ")


            )
            ->join("product_uom as b", "a.uom_id", "b.id")
            ->join("product_category as c", "a.category_id", "c.id")
            ->join("product_sub_category as d", "a.sub_category_id", "d.id")
            ->where("a.sub_category_id", $product->sub_category_id)
            ->where("a.active", 1)
            ->get();
        $related_products = $related_products->map(function ($item) {
            unset($item->temp_image);
            return $item;
        });
        $brand_products = DB::table("products as a")
            ->select(
                "a.*",
                "b.name as uom",
                "c.name as category",
                "d.name as sub_category",
                DB::raw("
                        (SELECT COALESCE(SUM(cs.stock), 0) 
                        FROM current_stock cs 
                        WHERE cs.product_id = a.id
                        ) as current_stock
                    ")

            )
            ->join("product_uom as b", "a.uom_id", "b.id")
            ->join("product_category as c", "a.category_id", "c.id")
            ->join("product_sub_category as d", "a.sub_category_id", "d.id")
            ->where("a.brand_id", $product->brand_id)
            ->where("a.active", 1)
            ->get();
        $brand_products = $brand_products->map(function ($item) {
            unset($item->temp_image);
            return $item;
        });
        return response()->json([
            "status" => true,
            "message" => "Product details fetched successfully",
            "data" => [
                "product" => $product,
                "product_images" => $images,
                "supplier" => $supplier,
                "related_products" => $related_products,
                "brand_products" => $brand_products
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
            $cart = DB::table("cart")
                ->where("product_id", $product->id)
                ->where("customer_id", $request->user["customer_id"])
                ->first();
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
        $faq_category = DB::table("faq_category")->get();
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
                ->join("product_category as pc", "a.category_id", "pc.id")
                ->join("product_sub_category as psc", "a.sub_category_id", "psc.id")
                ->join("product_sub_sub_category as pssc", "a.product_sub_sub_category", "pssc.id")
                ->leftJoin('product_type as pt', 'a.product_type_id', 'pt.id')
                ->where("a.is_home", 1)
                ->where("a.active", 1);
            $products = $filter->select(
                "a.id",
                "a.name",
                "a.gst",
                "a.cess_tax",
                "a.mrp",
                "a.image",
                "pt.name as product_type",
                "pc.name as category_name",
                "psc.name as sub_category",
                "pssc.name as sub_subcategory",
                DB::raw("COALESCE(b.base_price, a.base_price) as price"),
                DB::raw("
                        (SELECT COALESCE(SUM(cs.stock), 0) 
                        FROM current_stock cs 
                        WHERE cs.product_id = a.id
                        ) as current_stock
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
                    "category_name" => $product->category_name,
                    "sub_category" => $product->sub_category,
                    "sub_subcategory" => $product->sub_subcategory,
                    "product_type" => $product->product_type,
                    "gst" => $product->gst,
                    "cess_tax" => $product->cess_tax,
                    "base_price" => $product->price,
                    "mrp" => $product->mrp,
                    "discount" => $product->mrp > 0
                        ? round((($product->mrp - $product->price) / $product->mrp) * 100, 2)
                        : 0,
                    "tiers" => $tiers->values(),
                    "cart" => $cart,
                    "cart_status" => $cart ? true : false,
                    "wishlist_status" => $wishlist ? true : false,
                    "current_stock" => $product->current_stock,
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
                $amount = $value["price"] * $value["qty"];
                $gst = $value["gst"];
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

    public function addWishlistToCartBulk(Request $request)
    {
        try {
            $customerId = $request->user["customer_id"];
            $items = $request->wishlist_ids;
            if (empty($items) || !is_array($items)) {
                return response()->json([
                    'error' => true,
                    'message' => 'Please select wishlist items'
                ], 422);
            }
            foreach ($items as $row) {
                $wishlist = DB::table("wishlist")
                    ->where("id", $row['wishlist_id'])
                    ->where("customer_id", $customerId)
                    ->first();
                if (!$wishlist) continue;
                $qty = $row['qty'] ?? 1;
                $cartItem = DB::table("cart")
                    ->where("customer_id", $customerId)
                    ->where("product_id", $wishlist->product_id)
                    ->first();
                if ($cartItem) {
                    DB::table("cart")
                        ->where("id", $cartItem->id)
                        ->update([
                            'qty' => $cartItem->qty + $qty,
                            'updated_at' => now()
                        ]);
                } else {
                    DB::table("cart")->insert([
                        'customer_id' => $customerId,
                        'product_id' => $wishlist->product_id,
                        'qty' => $qty,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Selected wishlist items added to cart successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage()
            ], 500);
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
                ->join("product_uom as um", "p.uom_id", "um.id")
                ->where("c.customer_id", $customer_id)
                ->select(
                    "c.id",
                    "c.product_id",
                    "c.qty",
                    "p.image",
                    "p.name",
                    "p.base_price",
                    "p.per_uom",
                    "um.name as uom",
                    "p.mrp",
                    "p.gst",
                    "p.cess_tax",
                    DB::raw("
                        (SELECT COALESCE(SUM(cs.stock), 0) 
                        FROM current_stock cs 
                        WHERE cs.product_id = p.id
                        ) as current_stock
                    ")

                )
                ->get();
            foreach ($wishlist as $key => $item) {

                $tiers = DB::table("product_price")
                    ->where("product_id", $item->product_id)
                    ->orderBy("qty", "asc")
                    ->get();
                foreach ($tiers as $tkey => $tier) {
                    $tiers[$tkey]->final_price = $tier->price;
                }

                $wishlist[$key]->tiers = $tiers;
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

    public function Checkout(Request $request)
    {
        $cart = DB::table("cart")->where("customer_id", $request->user['customer_id'])->get();

        if ($cart->isEmpty()) {
            return response()->json([
                "status" => false,
                "message" => "Cart is empty",
                "data" => []
            ], 200);
        }

        $data = DB::table("cart as a")
            ->select(
                "a.*",
                "b.name",
                "b.base_price",
                "c.name as brand",
                "d.name as uom",
                "b.qty as prod_qty",
                "b.image",
                "b.gst",
                "b.cess_tax",
                "b.id as product_id"
            )
            ->join("products as b", "a.product_id", "b.id")
            ->leftJoin("product_brand as c", "b.brand_id", "c.id")
            ->join("product_uom as d", "b.uom_id", "d.id")
            ->where("customer_id", $request->user['customer_id'])
            ->get();

        foreach ($data as $item) {
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

        $customer_details = DB::table("customers as a")
            ->select(
                "a.*",
                "a.hold_amount as out_standing_due",
                "b.name as customer_name",
                "b.number as customer_number",
                "b.email as customer_email",
                "b.address as customer_address",
                "b.state as customer_state",
                "b.district as customer_district",
                "b.city as customer_city",
                "b.pincode as customer_pincode",
                DB::raw("
                (
                    COALESCE(a.wallet, 0) 
                    - COALESCE(a.used_wallet, 0) 
                    - COALESCE(a.hold_amount, 0)
                ) as active_amount
            ")
            )
            ->join("customer_users as b", "a.id", "=", "b.customer_id")
            ->where("b.customer_id", $request->user['customer_id'])
            ->first();

        return response()->json([
            "status" => true,
            "message" => "Checkout data fetched successfully",
            "data" => [
                "cart_items" => $data,
                "customer_details" => $customer_details
            ]
        ], 200);
    }

    public function placeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'delivery_address' => 'required',
            // 'paymode' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
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
            $challan_data = DB::table("suppliers")->where('id', 1)->first();
            $current_order_id = $challan_data->order_id;
            $next_order_id = $current_order_id + 1;
            $orders_id = $challan_data->order_series . $next_order_id;
            $my_order_id = $challan_data->order_series . $current_order_id;
            $order_id = DB::table("order_estimate")->insertGetId([
                "order_id" => $orders_id,
                "customer_id" => $request->user['customer_id'],
                "invoice_no" => $invoice_no,
                "pay_mode" => $request->pay_mode,
                "payment_status" => "Pending",
                "order_status" => "Pending",
                "total_amount" => $total_amount,
                "name" => $request->name ?? $customer->name,
                "number" => $request->delivery_phone ?? $customer->number,
                "email" => $request->delivery_email ?? $customer->email,
                "address" => $request->delivery_address ?? $customer->address,
                "state" => $request->delivery_state ?? $customer->state,
                "district" => $request->delivery_district ?? $customer->district,
                "city" => $request->delivery_city ?? $customer->city,
                "pincode" => $request->delivery_pincode ?? $customer->pincode,
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

                if (($holdAmount + $usedWallet  + $total_amount) > $wallet) {
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
            DB::table("suppliers")
                ->where('id', 1)
                ->update([
                    'order_id' => $next_order_id
                ]);
            DB::commit();
            try {

                $phone = $request->delivery_phone ?? $customer->number ?? null;

                if ($phone) {

                    $cleanPhone = preg_replace('/\D+/', '', $phone);
                    if (strlen($cleanPhone) == 10) $cleanPhone = '91' . $cleanPhone;
                    $sms_number = $cleanPhone;
                    $smsConfig = config('services.smswala');
                    $message =
                        "Dear Customer, your order estimate  has been successfully placed. Order Estimate ID: {$order_id} Total Amount: ₹{$total_amount} - Bulk Basket India.";
                    $url = "{$smsConfig['url']}?"
                        . "key={$smsConfig['key']}"
                        . "&campaign={$smsConfig['campaign']}"
                        . "&routeid={$smsConfig['routeid']}"
                        . "&type=text"
                        . "&contacts={$cleanPhone}"
                        . "&senderid={$smsConfig['sender']}"
                        . "&msg=" . urlencode($message)
                        . "&template_id=1707177546580814268"
                        . "&pe_id={$smsConfig['pe_id']}";

                    $response = Http::get($url);

                    if ($response->successful()) {
                        $sms_status = true;
                        $sms_message = "SMS sent successfully";
                        Log::info("✅ SMS sent: {$cleanPhone}");
                    } else {
                        $sms_status = false;
                        $sms_message = "SMS failed: " . $response->body();
                        Log::error("❌ SMS failed: " . $response->body());
                    }
                } else {
                    $sms_status = false;
                    $sms_message = "Phone number not found";
                }
            } catch (\Throwable $e) {
                $sms_status = false;
                $sms_message = "SMS Exception: " . $e->getMessage();
                Log::error("❌ SMS Exception: " . $e->getMessage());
            }
            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully.',
                'order_id' => $order_id,
                'invoice_no' => $invoice_no,
                'my_order_id' => $my_order_id,
                'delivery_date' => $request->delivery_date,
                'payment_method' => $request->pay_mode,
                'sms_status' => $sms_status,
                'sms_message' => $sms_message,
                'sms_number' => $sms_number
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
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
            $challan_data = DB::table("suppliers")->where('id', 1)->first();
            $current_order_id = $challan_data->order_id;
            $next_order_id = $current_order_id + 1;
            $orders_id = $challan_data->order_series . $next_order_id;
            $my_order_id = $challan_data->order_series . $current_order_id;
            $order_id = DB::table("order_estimate")->insertGetId([
                "order_id" => $orders_id,
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

            DB::table("suppliers")
                ->where('id', 1)
                ->update([
                    'order_id' => $next_order_id
                ]);
            DB::commit();

            return response()->json([
                'error' => false,
                'message' => 'Order placed successfully.',
                'order_id' => $order_id,
                'my_order_id' => $my_order_id,
                'delivery_date' => $request->delivery_date,
                'payment_method' => $request->pay_mode,
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

    public function removewishlist(Request $request)
    {
        try {

            $request->validate([
                'product_id' => 'required',
                'customer_id' => 'required',
            ]);

            $deleted = DB::table('wishlist')
                ->where('product_id', $request->product_id)
                ->where('customer_id', $request->user['customer_id'])
                ->delete();

            if ($deleted > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item removed successfully'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'No matching record found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing item',
                'error' => $e->getMessage()
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

    public function getCustomers(Request $request)
    {
        $customer = DB::table('customers as a')
            ->select(
                "a.id",
                "a.name as company_name",
                "a.number as company_number",
                "a.email as company_email",
                "a.address as company_address",
                "cu.name as customer_name",
                "cu.number as customer_number",
                "cu.password as customer_password"
            )
            ->join('customer_users as cu', 'a.id', "cu.customer_id")
            ->where('active', 1)->get();
        return response()->json([
            "Error" => False,
            "Customer list" => $customer,
        ]);
    }

    public function getWalletLedger(Request $request)
    {

        try {

            $customerId = $request->user["customer_id"];
            $company = DB::table("customers")
                ->where("id", $request->user["customer_id"])
                ->first();

            $wallet_statement = DB::table(DB::raw("(
    
                    -- 💰 1️⃣ Wallet Ledger (Credit)
                    SELECT 
                        wl.id,
                        wl.created_at,
                        wl.amount,
                        'credit' AS type,
                        wl.invoice_no,
                        'Sale (GST)' AS particular,
                        wl.pay_date,
                        wl.pay_mode,
                        wl.remarks,
                        NULL AS order_id

                    FROM wallet_ledger wl
                    WHERE wl.customer_id = $customerId

                    UNION ALL

                    -- 💸 2️⃣ Orders paid via wallet (Debit)
                    SELECT 
                        o.id,
                        o.created_at,
                        o.total_amount AS amount,
                        'debit' AS type,
                        o.invoice_no,
                        'Payment' AS particular,
                        o.created_at AS pay_date,
                        o.pay_mode,
                        'Order Generated' AS remarks,
                        oe.order_id AS order_id

                    FROM orders o
                    LEFT JOIN order_estimate oe 
                        ON oe.id = o.estimate_id

                    WHERE o.customer_id = $customerId
                    AND o.pay_mode = 'wallet'

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
                        'Interest added to wallet' AS remarks,
                        NULL AS order_id

                    FROM order_payment_intrest i
                    INNER JOIN orders o 
                        ON o.id = i.order_id
                    INNER JOIN customers c 
                        ON c.id = o.customer_id

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

    public function AddWalletAmount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'amount' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $customer = DB::table("customers")
                ->where("id", $request->customer_id)
                ->first();

            if (!$customer) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
            if ($customer->active != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account currently not active'
                ], 403);
            }
            $invoice_no = 'VOU-' . $request->customer_id . date('YmdHis');
            DB::table('wallet_ledger')->insert([
                'customer_id' => $request->customer_id,
                'amount' => $request->amount,
                'pay_mode' => "Online",
                'pay_date'  => now(),
                'supplier_id' => 1,
                'invoice_no' => $invoice_no,
                'remarks' => "Add Online From Mobile",
                'created_at' => now(),
                'updated_at' => now()
            ]);
            DB::table("customers")
                ->where("id", $request->customer_id)
                ->decrement("used_wallet", $request->amount);
            $ledgerAmount = $request->amount;

            if ($ledgerAmount > 0) {

                $orders = DB::table('orders')
                    ->where('customer_id', $request->customer_id)
                    ->where('intrest_amount', '>', 0)
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($orders as $order) {

                    if ($ledgerAmount <= 0) break;

                    $currentInterest = (float) $order->intrest_amount;

                    if ($ledgerAmount >= $currentInterest) {

                        DB::table('orders')->where('id', $order->id)->update([
                            'intrest_amount' => 0,
                            'updated_at' => now()
                        ]);

                        $ledgerAmount -= $currentInterest;
                    } else {

                        DB::table('orders')->where('id', $order->id)->update([
                            'intrest_amount' => $currentInterest - $ledgerAmount,
                            'updated_at' => now()
                        ]);

                        $ledgerAmount = 0;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Wallet ledger added successfully',
                'data' => [
                    'invoice_no' => $invoice_no,
                    'amount' => $request->amount
                ]
            ], 200);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function customerProfileApi(Request $request)
    {
        $company = DB::table("customers")
            ->where("id", $request->user["customer_id"])
            ->first();

        $customer_details = DB::table("customer_users")
            ->where("id", $request->user["id"])
            ->first();

        $order_mst = DB::table("orders as a")
            ->select(
                "a.*",
                "b.shipping_status as status",
                "b.subtotal",
                "b.id as order_supplier_id",
                "c.name as supplier_name",
                "c.number as supplier_number"
            )
            ->join("orders_supplier as b", "a.id", "b.order_id")
            ->leftJoin("supplier_users as c", "a.user_id", "=", "c.id")
            ->where("a.customer_id", $request->user["customer_id"])
            ->orderBy("a.id", "desc")
            ->get();
        $order_estimate_his = DB::table("order_estimate as a")
            ->select(
                "a.*",
                "b.shipping_status as status",
                "b.subtotal",
                "b.id as order_supplier_id",
            )
            ->join("orders_supplier as b", "a.id", "b.order_id")->where('order_status', "Pending")
            ->where("a.customer_id", $request->user["customer_id"])
            ->orderBy("a.id", "desc")
            ->get();

        $customer_document = DB::table("customer_document")
            ->where("customer_id", $request->user["customer_id"])
            ->get();

        $orderEstimate = DB::table('order_estimate')
            ->select(DB::raw("
        SUM(CASE WHEN order_status IN ('Pending', 'Processing') THEN 1 ELSE 0 END) as pending_order
        "))
            ->where("customer_id", $request->user['customer_id'])
            ->first();
        $order_count = DB::table("orders")
            ->selectRaw("
            COUNT(*) as total_order,
            SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_order,
            SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as processing_order,
            SUM(CASE WHEN order_status = 'packed' THEN 1 ELSE 0 END) as packed_order,
            SUM(CASE WHEN order_status = 'dispatch' THEN 1 ELSE 0 END) as dispatch_order,
            SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_order
        ")
            ->where("customer_id", $request->user['customer_id'])
            ->first();


        $orders = DB::table('orders')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->where('customer_id', $request->user['customer_id'])
            ->whereYear('created_at', date('Y'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('total', 'month');

        $monthlyOrders = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyOrders[] = $orders->get($m, 0);
        }

        $id = $request->user['customer_id'];

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
        return response()->json([
            'status' => true,
            'data' => [
                'company' => $company,
                'customer_details' => $customer_details,
                'order_mst' => $order_mst,
                'customer_document' => $customer_document,
                'order_count' => $order_count,
                'monthlyOrders' => $monthlyOrders,
                'orderEstimate' => $orderEstimate,
                'order_estimate_his' => $order_estimate_his,
                'wallet_statement' => $wallet_statement
            ]
        ]);
    }

    public function createHdfcOrder(Request $request)
    {
        $order = DB::table('order_estimate')
            ->where('invoice_no', $request->invoice_no)
            ->first();
        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Invalid Order']);
        }

        $payload = [
            "order_id" => $order->invoice_no,
            "amount" => (int) round($order->total_amount),
            "customer_id" => (string) $order->customer_id,
            "customer" => [
                "name" => $order->name,
                "email" => $order->email,
                "phone" => $order->number,
            ],
            "return_url" => "https://bulkbasketindia.com/payment-processing?order_id={$order->invoice_no}",
            "notify_url" => "https://bulkbasketindia.com/payment/hdfc/webhook",
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(config('services.hdfc.api_key')),
            'x-merchantid'  => config('services.hdfc.merchant_id'),
            'x-resellerid'  => config('services.hdfc.reseller_id'),
            'Content-Type'  => 'application/json',
        ])->post(config('services.hdfc.base_url') . '/orders', $payload);

        return response()->json([
            'payment_url' => $response['payment_links']['web']
        ]);
    }

    public function checkStatus($invoiceNo)
    {
        $order = DB::table('order_estimate')
            ->where('invoice_no', $invoiceNo)
            ->first();

        if (!$order) {
            return response()->json(['payment_status' => 'invalid']);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(config('services.hdfc.api_key')),
            'x-merchantid'  => config('services.hdfc.merchant_id'),
            'x-customerid'  => $order->customer_id,
            'x-resellerid'  => config('services.hdfc.reseller_id'),
            'Content-Type'  => 'application/json',
        ])->get(config('services.hdfc.base_url') . "/orders/{$invoiceNo}");

        $hdfcStatus = $response['status'];

        // ✅ MAP HDFC STATUS
        $map = [
            'CHARGED' => 'success',
            'PENDING_VBV' => 'pending',
            'AUTHORIZING' => 'pending',
            'STARTED' => 'pending',
            'JUSPAY_DECLINED' => 'failed',
            'AUTHENTICATION_FAILED' => 'failed',
            'AUTHORIZATION_FAILED' => 'failed',
            'AUTO_REFUNDED' => 'refunded',
            'VOIDED' => 'cancelled',
        ];

        $finalStatus = $map[$hdfcStatus] ?? 'pending';

        DB::table('order_estimate')
            ->where('invoice_no', $invoiceNo)
            ->update(['payment_status' => $finalStatus]);

        return response()->json([
            "order_id" => $order->invoice_no,
            "transaction_id" => $response['id'] ?? null,
            "amount" => $order->total_amount,
            "currency" => "INR",
            "payment_status" => $finalStatus,
            "hdfc_status" => $hdfcStatus,
            "payment_mode" => $response['payment_method'] ?? null,
            "response_message" => $finalStatus === 'success'
                ? "Transaction Successful"
                : "Transaction Failed",
            "transaction_date" => now()->format('Y-m-d H:i:s')
        ]);
    }

    public function webhook(Request $request)
    {
        Log::info('HDFC WEBHOOK', $request->all());

        $invoiceNo = $request->order_id;

        $order = DB::table('order_estimate')
            ->where('invoice_no', $invoiceNo)
            ->first();

        if (!$order) {
            return response()->json(['status' => 'ignored']);
        }

        // 🔐 Re-verify using Order Status API
        $statusResponse = $this->checkStatus($invoiceNo);
        $finalStatus = $statusResponse->getData()->payment_status;

        if ($finalStatus === 'success') {
            DB::table('order_estimate')
                ->where('invoice_no', $invoiceNo)
                ->update([
                    'payment_status' => 'success',
                    'order_status' => 'Confirmed'
                ]);

            DB::table("cart")
                ->where("customer_id", $order->customer_id)
                ->delete();
        }

        return response()->json(['status' => 'ok']);
    }
}
