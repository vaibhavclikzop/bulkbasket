<?php

namespace App\Http\Controllers\ApiController;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Log;


class ApiController extends Controller
{

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

    // public function getProducts(Request $request)
    // {
    //     $query = $request->input("query");
    //     $productsQuery = DB::table("products as a")
    //         ->select(
    //             "a.*",
    //             "b.name as uom",
    //             "c.name as category",
    //             "d.name as sub_category",
    //             "e.name as brand"
    //         )
    //         ->join("product_uom as b", "a.uom_id", "b.id")
    //         ->join("product_category as c", "a.category_id", "c.id")
    //         ->join("product_sub_category as d", "a.sub_category_id", "d.id")
    //         ->leftJoin("product_brand as e", "a.brand_id", "e.id")
    //         ->where("a.active", 1);
    //     if ($query) {
    //         $productsQuery->where(function ($q) use ($query) {
    //             $q->where('a.name', 'like', '%' . $query . '%')
    //                 ->orWhere('a.description', 'like', '%' . $query . '%')
    //                 // ->orWhere('c.name', 'like', '%' . $query . '%')
    //                 // ->orWhere('d.name', 'like', '%' . $query . '%')
    //                 ->orWhere('a.tags', 'like', '%' . $query . '%');
    //         });
    //     }
    //     $products = $productsQuery->paginate(50);
    //     foreach ($products as $product) {
    //         $product->details = DB::table("product_price")
    //             ->where("product_id", $product->id)
    //             ->get();
    //     }
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'All products retrieved successfully.',
    //         'data' => $products
    //     ]);
    // }

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
            ->select("a.*", "b.name as uom", "c.name as category", "d.name as sub_category")
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

        $product->details = DB::table("product_price")->where("product_id", $product->id)->get();

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

    public function shopAddToCart(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'qty' => 'nullable|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $qty = $request->qty ?? 1;

        $customerId = $request->user['customer_id'] ?? null;
        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Customer ID not found.',
            ], 401);
        }

        try {
            $product = DB::table("products")->where("id", $request->product_id)->first();
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => "Product not found"
                ], 404);
            }

            $cart = DB::table("cart")
                ->where("product_id", $request->product_id)
                ->where("customer_id", $customerId)
                ->first();

            if ($cart) {
                if ($request->qtyType === "plus") {
                    DB::table("cart")
                        ->where("product_id", $request->product_id)
                        ->where("customer_id", $customerId)
                        ->increment("qty", 1);
                } elseif ($request->qtyType === "minus") {
                    DB::table("cart")
                        ->where("product_id", $request->product_id)
                        ->where("customer_id", $customerId)
                        ->decrement("qty", 1);

                    if ($cart->qty - 1 <= 0) {
                        DB::table("cart")
                            ->where("product_id", $request->product_id)
                            ->where("customer_id", $customerId)
                            ->delete();
                    }
                } else {
                    if ($qty <= 0) {
                        DB::table("cart")
                            ->where("product_id", $request->product_id)
                            ->where("customer_id", $customerId)
                            ->delete();
                    } else {
                        DB::table("cart")
                            ->where("product_id", $request->product_id)
                            ->where("customer_id", $customerId)
                            ->update([
                                "qty" => $qty,
                            ]);
                    }
                }
            } else {
                DB::table("cart")->insert([
                    "product_id" => $request->product_id,
                    "qty" => $qty,
                    "customer_id" => $customerId,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function shopAddToWhishlist(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            // 'qty' => 'nullable|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $qty = $request->qty ?? 1;

        $customerId = $request->user['customer_id'] ?? null;
        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Customer ID not found.',
            ], 401);
        }

        try {
            $product = DB::table("products")->where("id", $request->product_id)->first();
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => "Product not found"
                ], 404);
            }

            $cart = DB::table("wishlist")
                ->where("product_id", $request->product_id)
                ->where("customer_id", $customerId)
                ->first();

            if ($cart) {
                if ($request->qtyType === "plus") {
                    DB::table("wishlist")
                        ->where("product_id", $request->product_id)
                        ->where("customer_id", $customerId)
                        ->increment("qty", 1);
                } elseif ($request->qtyType === "minus") {
                    DB::table("wishlist")
                        ->where("product_id", $request->product_id)
                        ->where("customer_id", $customerId)
                        ->decrement("qty", 1);

                    if ($cart->qty - 1 <= 0) {
                        DB::table("wishlist")
                            ->where("product_id", $request->product_id)
                            ->where("customer_id", $customerId)
                            ->delete();
                    }
                } else {
                    if ($qty <= 0) {
                        DB::table("wishlist")
                            ->where("product_id", $request->product_id)
                            ->where("customer_id", $customerId)
                            ->delete();
                    } else {
                        DB::table("wishlist")
                            ->where("product_id", $request->product_id)
                            ->where("customer_id", $customerId)
                            ->update([
                                "qty" => $qty,
                            ]);
                    }
                }
            } else {
                DB::table("wishlist")->insert([
                    "product_id" => $request->product_id,
                    "qty" => $qty,
                    "customer_id" => $customerId,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function cartApi(Request $request)
    {
        try {
            $customer_id = $request->user['customer_id'] ?? null;

            if (!$customer_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer ID not found in request.',
                ], 401);
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
                ->where("a.customer_id", $customer_id)
                ->where("a.qty", ">", 0)
                ->get();
            foreach ($data as $item) {
                $tiers = DB::table("product_price")
                    ->where("product_id", $item->product_id)
                    ->orderBy("qty", "asc")
                    ->get();
                $item->details = $tiers;
                foreach ($tiers as $tier) {
                    if ($item->qty >= $tier->qty) {
                        $item->mrp = $tier->price;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart data fetched successfully.',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function whishList(Request $request)
    {
        try {
            $customer_id = $request->user['customer_id'] ?? null;

            if (!$customer_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer ID not found in request.',
                ], 401);
            }

            $data = DB::table("wishlist as a")
                ->select(
                    "a.*",
                    "b.name",
                    "b.base_price",
                    "c.name as brand",
                    "d.name as uom",
                    "b.qty as prod_qty",
                    "b.image",
                    "b.gst",
                    "b.mrp",
                    "b.cess_tax",
                    "b.id as product_id"
                )
                ->join("products as b", "a.product_id", "b.id")
                ->leftJoin("product_brand as c", "b.brand_id", "c.id")
                ->join("product_uom as d", "b.uom_id", "d.id")
                ->where("a.customer_id", $customer_id)
                ->where("a.qty", ">", 0)
                ->get();
            foreach ($data as $item) {
                $tiers = DB::table("product_price")
                    ->where("product_id", $item->product_id)
                    ->orderBy("qty", "asc")
                    ->get();
                $item->details = $tiers;
                foreach ($tiers as $tier) {
                    if ($item->qty >= $tier->qty) {
                        $item->mrp = $tier->price;
                    }
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Cart data fetched successfully.',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getCartByCustomer(Request $request)
    {
        $customer_id = $request->user['customer_id'];

        $cartItems = DB::table('cart')
            ->select('product_id', 'qty')
            ->where('customer_id', $customer_id)
            ->get();

        $totalQty = $cartItems->count('product_id');

        return response()->json([
            'cart_items' => $cartItems,
            'total_qty' => $totalQty
        ]);
    }

    public function apiLogout(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Token not provided.'], 401);
        }
        $user = DB::table('customer_users')->where('web_token', $token)->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid token.'], 401);
        }
        DB::table('customer_users')
            ->where('id', $user->id)
            ->update(['web_token' => '']);
        return response()->json(['message' => 'Logout successful.'], 200);
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
                "b.name as customer_name",
                "b.number as customer_number",
                "b.email as customer_email",
                "b.address as customer_address",
                "b.state as customer_state",
                "b.district as customer_district",
                "b.city as customer_city",
                "b.pincode as customer_pincode"
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


    // public function SaveOrder(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         // 'delivery_address' => 'required',
    //         // 'paymode' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()->first()
    //         ], 422);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $cart = DB::table("cart as a")
    //             ->select("a.*", "b.supplier_id", "b.base_price as mrp", "b.name as product", "b.description", "b.cess_tax", "b.gst")
    //             ->join("products as b", "a.product_id", "=", "b.id")
    //             ->where("a.customer_id", $request->user["customer_id"])
    //             ->get()
    //             ->groupBy("supplier_id");

    //         if ($cart->isEmpty()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Cart is empty.'
    //             ], 400);
    //         }

    //         // Tier pricing
    //         foreach ($cart as $k => $v) {
    //             foreach ($v as $item) {
    //                 $tiers = DB::table("product_price")
    //                     ->where("product_id", $item->product_id)
    //                     ->orderBy("qty", "asc")
    //                     ->get();

    //                 foreach ($tiers as $tier) {
    //                     if ($item->qty >= $tier->qty) {
    //                         $item->mrp = $tier->price;
    //                     }
    //                 }
    //             }
    //         }

    //         $customer = $request->delivery_address === "Office"
    //             ? DB::table("customers")->where("id", $request->user["customer_id"])->first()
    //             : DB::table("customer_users")->where("id", $request->user["id"])->first();

    //         $total_amount = 0;
    //         $invoice_no = 'INV-' . $request->user['customer_id'] . date('YmdHis');

    //         $order_id = DB::table("order_estimate")->insertGetId([
    //             "customer_id" => $request->user['customer_id'],
    //             "invoice_no" => $invoice_no,
    //             "pay_mode" => $request->pay_mode,
    //             "payment_status" => "Pending",
    //             "order_status" => "Pending",
    //             "total_amount" => $total_amount,
    //             "name" => $request->name ?? $customer->name,
    //             "number" => $request->delivery_phone ?? $customer->number,
    //             "email" => $customer->email,
    //             "address" => $request->delivery_address ?? $customer->address,
    //             "state" => $request->delivery_state ?? $customer->state,
    //             "district" => $request->delivery_district ?? $customer->district,
    //             "city" => $request->delivery_city ?? $customer->city,
    //             "pincode" => $request->delivery_pincode ?? $customer->pincode,
    //             "remarks" => $request->remarks ?? null,
    //             "delivery_date" => $request->delivery_date ?? null,
    //         ]);

    //         foreach ($cart as $supplier_id => $items) {
    //             $supplierSubtotal = $items->sum(fn($item) => $item->mrp * $item->qty);

    //             $orderSupplierId = DB::table("orders_supplier")->insertGetId([
    //                 "order_id" => $order_id,
    //                 "supplier_id" => $supplier_id,
    //                 "subtotal" => $supplierSubtotal,
    //                 "shipping_status" => "pending",
    //             ]);

    //             $gst_total = 0;
    //             $cess_total = 0;

    //             foreach ($items as $item) {
    //                 DB::table("order_estimate_item")->insert([
    //                     "supplier_id" => $supplier_id,
    //                     "order_id" => $order_id,
    //                     "product_id" => $item->product_id,
    //                     "qty" => $item->qty,
    //                     "price" => $item->mrp,
    //                     "cess_tax" => $item->cess_tax,
    //                     "gst" => $item->gst,
    //                     "name" => $item->product,
    //                     "description" => $item->description,
    //                 ]);

    //                 $gst_total += $item->mrp * $item->qty * $item->gst / 100;
    //                 $cess_total += $item->mrp * $item->qty * $item->cess_tax / 100;
    //             }

    //             DB::table("orders_supplier")->where("id", $orderSupplierId)->update([
    //                 "subtotal" => $supplierSubtotal + $gst_total + $cess_total,
    //             ]);

    //             $total_amount += $supplierSubtotal + $gst_total + $cess_total;
    //         }

    //         DB::table('order_estimate')->where('id', $order_id)->update([
    //             'total_amount' => $total_amount
    //         ]);
    //         $customer = DB::table("customers")->where("id", $request->user["customer_id"])->first();

    //         if ($request->pay_mode === 'wallet') {

    //             $wallet = (float)($customer->wallet ?? 0);
    //             $holdAmount = (float)($customer->hold_amount ?? 0);
    //             $usedWallet = (float)($customer->used_wallet ?? 0);

    //             if (($holdAmount + $usedWallet + $total_amount) > $wallet) {
    //                 DB::rollBack();
    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => 'Wallet amount is less than order total.'
    //                 ], 400);
    //             }

    //             DB::table('order_estimate')->where('id', $order_id)->update([
    //                 'payment_status' => "Hold"
    //             ]);

    //             DB::table("customers")
    //                 ->where("id", $request->user['customer_id'])
    //                 ->increment("hold_amount", $total_amount);
    //         }

    //         DB::table("cart")
    //             ->where("customer_id", $request->user['customer_id'])
    //             ->delete();

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Order placed successfully.',
    //             'order_id' => $order_id,
    //             'invoice_no' => $invoice_no
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Server Error: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function SaveOrder(Request $request)
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

            $order_id = DB::table("order_estimate")->insertGetId([
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


    public function getInvoiceData(Request $request, $invoiceNo)
    {

        $customer = DB::table("customer_users")
            ->where("customer_id", $request->user['customer_id'])
            ->first();
        $order_mst = DB::table("order_estimate as a")
            ->select(
                "a.*",
                "b.shipping_status as status",
                "b.subtotal",
                "b.id as supplier_order_id"
            )
            ->join("orders_supplier as b", "a.id", "=", "b.order_id")
            ->where("a.invoice_no", $invoiceNo)
            ->first();

        if (!$order_mst) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }
        $orders_item = DB::table("order_estimate_item as oi")
            ->select(
                "oi.*",
                "p.hsn_code",
                "p.name as product_name",
                "u.name as uom_name"
            )
            ->join("products as p", "oi.product_id", "=", "p.id")
            ->leftJoin("product_uom as u", "p.uom_id", "=", "u.id")
            ->where("oi.order_id", $order_mst->id)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Invoice data fetched successfully',
            'data' => [
                'order_mst' => $order_mst,
                'orders_item' => $orders_item,
                'customer' => $customer,
            ]
        ]);
    }

    public function getInvoiceBill(Request $request, $id)
    {
        $customer = DB::table("customer_users")
            ->where("customer_id", $request->user['customer_id'])
            ->first();
        $order_mst = DB::table("orders as a")
            ->select("a.*", "b.shipping_status as status", "b.subtotal", "b.id")
            ->join("orders_supplier as b", "a.id", "b.order_id")
            ->where("b.id", $id)
            ->orderBy("a.id", "desc")
            ->first();

        if (!$order_mst) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $orders_item = DB::table("orders_item as oi")
            ->select(
                "oi.*",
                "p.hsn_code",
                "p.name as product_name",
                "u.name as uom_name"
            )
            ->join("products as p", "oi.product_id", "=", "p.id")
            ->leftJoin("product_uom as u", "p.uom_id", "=", "u.id")
            ->where("oi.order_id", $id)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Invoice data fetched successfully',
            'data' => [
                'order_mst' => $order_mst,
                'orders_item' => $orders_item,
                'customer' => $customer,
            ]
        ]);
    }

    public function removeItem(Request $request)
    {
        try {
            $customer_id = $request->user['id'];
            $productId = $request->input('product_id');
            if (!$productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product ID is required'
                ], 400);
            }

            DB::table('cart')
                ->where('customer_id', $customer_id)
                ->where('product_id', $productId)
                ->delete();

            return response()->json([
                'success' => true,
                "customer" => $request->user['id'],
                'message' => 'Item removed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing item',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function removewishlist(Request $request)
    {
        try {
            $product_id = $request->input('product_id');
            $customer_id = $request->input('customer_id');
            if (!$product_id || !$customer_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product ID & Customer ID are required'
                ], 400);
            }
            $deleted = DB::table('wishlist')
                ->where('product_id', $product_id)
                ->where('customer_id', $customer_id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item removed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No matching record found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing item',
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

        // $wallet_statement = DB::table(DB::raw("(
        //         -- 💰 1️⃣ Wallet Ledger (Normal Credits)
        //         SELECT 
        //             id,
        //             created_at,
        //             amount,
        //             'credit' AS type,
        //             invoice_no,
        //             'Sale (GST)' AS particular,
        //             created_at AS pay_date,
        //             pay_mode,
        //             invoice_no AS wallet_no,
        //             remarks
        //         FROM wallet_ledger
        //         WHERE customer_id = $id
        //         AND pay_mode NOT LIKE '%Interest%'
        //         AND pay_mode <> 'Credit_limit'

        //         UNION ALL

        //         -- 🧾 2️⃣ Wallet Ledger (Interest Charges - Internal)
        //         SELECT 
        //             id,
        //             created_at,
        //             amount,
        //             'Interest' AS type,
        //             invoice_no,
        //             'Interest Charge' AS particular,
        //             pay_date,
        //             pay_mode,
        //             invoice_no AS wallet_no,
        //             remarks
        //         FROM wallet_ledger
        //         WHERE customer_id = $id
        //         AND pay_mode LIKE '%Interest%'

        //         UNION ALL

        //         -- 💸 3️⃣ Orders paid using Wallet (Debit)
        //         SELECT 
        //             id,
        //             created_at,
        //             total_amount AS amount,
        //             'debit' AS type,
        //             invoice_no,
        //             'Payment' AS particular,
        //             created_at AS pay_date,
        //             pay_mode,
        //             NULL AS wallet_no,
        //             'Order Generated' AS remarks
        //         FROM orders
        //         WHERE customer_id = $id 
        //         AND pay_mode = 'wallet'

        //         UNION ALL

        //         -- 🪙 4️⃣ Interest Earned (From order_payment_intrest table)
        //         SELECT 
        //             i.id,
        //             i.created_at,
        //             i.intrest_value AS amount,
        //             'Interest' AS type,
        //             o.invoice_no,
        //             'Interest (Wallet)' AS particular,
        //             i.created_at AS pay_date,
        //             'wallet' AS pay_mode,
        //             o.invoice_no AS wallet_no,
        //             'Interest added to wallet' AS remarks
        //         FROM order_payment_intrest i
        //         INNER JOIN orders o ON o.id = i.order_id
        //         INNER JOIN customers c ON c.id = o.customer_id
        //         WHERE c.id = $id
        //     ) AS wallet_union"))
        //     ->orderBy('created_at', 'asc')
        //     ->get();


        // // ✅ Calculate running balance
        // $balance = 0;

        // foreach ($wallet_statement as $entry) {
        //     if ($entry->type === 'credit' || $entry->type === 'Interest') {
        //         $balance += $entry->amount;
        //     } elseif ($entry->type === 'debit') {
        //         $balance -= $entry->amount;
        //     }
        //     $entry->balance = $balance;
        // }

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

    public function BannerApi(Request $request)
    {
        try {
            $data = DB::table("sliders1")->orderBy("id", "desc")->get();

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


    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function requestProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'phone'        => 'required',
            'remarks'      => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }
        try {
            DB::table('request_for_product')->insert([
                'product_name' => $request->product_name,
                'phone'        => $request->phone,
                'name'        => $request->name,
                'remarks'      => $request->remarks,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Product request submitted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function requestListProduct(Request $request)
    {
        $data = DB::table('request_for_product')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json([
            'status' => true,
            'message' => 'Request list retrieved successfully.',
            'data' => $data
        ]);
    }

    public function getMessages($customerId)
    {
        $messages = ChatMessage::where('customer_id', $customerId)
            ->orderBy('created_at')
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {

        $chat = ChatMessage::create([
            'customer_id' => $request['customer_id'],
            'supplier_id' => $request['supplier_id'],
            'sender_type' => 'customer',
            'message'     => $request['message'],
            'status'      => 'sent',
        ]);
        broadcast(new MessageSent($chat))->toOthers();
        return response()->json($chat);
    }

    public function requestForPrice(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'items.*.product_id' => 'required',
                'items.*.price' => 'required',
                'items.*.qty' => 'required|min:1',
                'items.*.customer_number' => 'required',
            ]);
            $customerNumber = $request->items[0]['customer_number'];
            $order_estimate_id = $request->items[1]['order_estimate_id'];
            $supplierId = 1;
            $mstId = DB::table('request_for_price_mst')->insertGetId([
                'customer_number' => $customerNumber,
                'order_estimate_id' => $order_estimate_id,
                'supplier_id' => $supplierId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $details = collect($request->items)->map(function ($item) use ($mstId) {
                return [
                    'request_mst_id' => $mstId,
                    'product_id' => $item['product_id'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
            DB::table('request_for_price_det')->insert($details);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Request for price saved successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function orderEstimate(Request $request, $id)
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
                        'products.image as product_image'
                    )
                    ->where('order_estimate_item.order_id', $order->id)
                    ->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Order estimate(s) retrieved successfully.',
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function orderEstimateApp(Request $request, $customerId)
    {
        try {
            $query = DB::table('order_estimate');

            if ($customerId) {
                $query->where('order_estimate.customer_id', $customerId);
            }

            $orders = $query->orderBy('order_estimate.id', 'desc')->get();

            foreach ($orders as $order) {
                $order->items = DB::table('order_estimate_item')
                    ->join('products', 'order_estimate_item.product_id', '=', 'products.id')
                    ->select(
                        'order_estimate_item.*',
                        'products.name as product_name',
                        'products.image as product_image'
                    )
                    ->where('order_estimate_item.order_id', $order->id)
                    ->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Order estimate(s) retrieved successfully.',
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function orderApp(Request $request, $customerId)
    {
        try {
            $query = DB::table('orders');

            if ($customerId) {
                $query->where('orders.customer_id', $customerId);
            }

            $orders = $query->orderBy('orders.id', 'desc')->get();

            foreach ($orders as $order) {
                $order->items = DB::table('orders_item')
                    ->join('products', 'orders_item.product_id', '=', 'products.id')
                    ->select(
                        'orders_item.*',
                        'products.name as product_name',
                        'products.image as product_image'
                    )
                    ->where('orders_item.order_id', $order->id)
                    ->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Order retrieved successfully.',
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function walletLedger(Request $request, $customerId)
    {
        if (!$customerId) {
            return response()->json([
                'status' => false,
                'message' => 'Customer ID required'
            ], 400);
        }
        $company = DB::table("customers")
            ->where("id", $customerId)
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
            'status' => true,
            'customer_id' => $customerId,
            'company' => $company,
            'data' => $wallet_statement
        ]);
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
}
