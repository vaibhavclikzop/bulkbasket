<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\WareHouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;

class Masters extends Controller
{
    public function Customers(Request $request)
    {
        return view("admin.customers");
    }

    public function Suppliers(Request $request)
    {
        $data =  DB::table("suppliers")->orderBy("id", "desc")->get();
        $emailTemp = DB::table("email_template")->get();
        return view("admin.suppliers", compact("data", "emailTemp"));
    }


    public function SaveSuppliers(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'number' => 'required|digits:10',
        //     'name' => 'required',
        //     'password' => 'required',
        //     'company_name' => 'required',
        //     'company_number' => 'required',
        // ]);

        // if ($validator->fails()) {
        //     $messages = $validator->errors();
        //     $count = 0;
        //     foreach ($messages->all() as $error) {
        //         if ($count == 0)
        //             return redirect()->back()->with('error', $error);
        //         $count++;
        //     }
        // }

        try {
            DB::beginTransaction();

            // 🧠 Check if editing
            if ($request->has('id') && !empty($request->id)) {
                // UPDATE supplier
                DB::table('suppliers')->where('id', $request->id)->update([
                    "name" => $request->company_name,
                    "number" => $request->company_number,
                    "email" => $request->company_email,
                    "gst" => $request->company_gst,
                    "address" => $request->company_address,
                    "state" => $request->company_state,
                    "city" => $request->company_city,
                    "district" => $request->company_district,
                    "pincode" => $request->company_pincode,
                    "email_temp_id" => $request->email_temp_id,
                ]);
                DB::commit();
                return redirect()->back()->with("success", "Updated Successfully");
            } else {
                $supplier_id = DB::table('suppliers')->insertGetId([
                    "name" => $request->company_name,
                    "number" => $request->company_number,
                    "email" => $request->company_email,
                    "gst" => $request->company_gst,
                    "address" => $request->company_address,
                    "state" => $request->company_state,
                    "city" => $request->company_city,
                    "district" => $request->company_district,
                    "pincode" => $request->company_pincode,
                    "email_temp_id" => $request->email_temp_id,
                ]);
                $role_id = DB::table("supplier_role")->insertGetId([
                    "name" => "admin",
                    "supplier_id" => $supplier_id,
                ]);
                DB::table('supplier_users')->insert([
                    "name" => $request->name,
                    "number" => $request->number,
                    "email" => $request->email,
                    "address" => $request->address,
                    "state" => $request->state,
                    "city" => $request->city,
                    "district" => $request->district,
                    "pincode" => $request->pincode,
                    "password" => $request->password,
                    "role_id" => $role_id,
                    "supplier_id" => $supplier_id,
                ]);
                DB::commit();
                return redirect()->back()->with("success", "Saved Successfully");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function SupplierUsers(Request $request, $id)
    {
        $data =  DB::table("supplier_users")
            ->where("supplier_id", $id)
            ->orderBy("id", "desc")
            ->get();
        return view("admin.suppliers-users", compact("data"));
    }

    public function ProductType(Request $request)
    {
        $data = DB::table("product_type as a")
            ->where("a.supplier_id", $request->user['supplier_id'])
            ->groupBy("a.id", "a.name", "a.active", "a.supplier_id", "a.created_at", "a.updated_at")
            ->get();
        return view("suppliers.product-type", compact("data"));
    }


    public function SaveProductType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
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
            $check = DB::table("product_type")->where("name", $request->name)->where("supplier_id", $request->user["supplier_id"])->first();
            if ($check) {
                return redirect()->back()->with("error", "Product Type name already added");
            }
            if ($request->id) {
                DB::table('product_type')->where("id", $request->id)->update(array(

                    "name" => $request->name,
                    "supplier_id" => $request->user['supplier_id'],
                ));
            } else {
                DB::table('product_type')->insertGetId(array(
                    "name" => $request->name,
                    "supplier_id" => $request->user['supplier_id'],
                ));
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function ProductBrand(Request $request)
    {
        $data = DB::table("product_brand as a")
            ->select(
                "a.*",
                DB::raw("COUNT(b.id) as total_products")
            )
            ->leftJoin("products as b", "a.id", "=", "b.brand_id")
            ->where("a.supplier_id", $request->user['supplier_id'])
            ->groupBy("a.id", "a.name", "a.image", "a.supplier_id", "a.created_at", "a.updated_at")
            ->get();
        return view("suppliers.product-brand", compact("data"));
    }


    public function deleteProductBrand(Request $request)
    {
        DB::table("product_brand")->where("id", $request->id)->delete();
        return redirect()->back()->with("success", "Save Successfully");
    }

    public function SaveProductBrand(Request $request)
    {
        $validator = Validator::make($request->all(), [


            'name' => 'required',


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
        $file = "";
        if ($request->hasFile('file')) {
            $file = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move('master images', $file);
        } else {
            if ($request->id) {
                $product_category =  DB::table("product_brand")->where("id", $request->id)->first();
                $file = $product_category->image;
            }
        }

        try {
            $check = DB::table("product_brand")->where("name", $request->name)->where("supplier_id", $request->user["supplier_id"])->first();
            if ($check) {
                return redirect()->back()->with("error", "Brand name already added");
            }

            if ($request->id) {
                DB::table('product_brand')->where("id", $request->id)->update(array(

                    "name" => $request->name,
                    "image" => $file,
                    "supplier_id" => $request->user['supplier_id'],


                ));
            } else {
                DB::table('product_brand')->insertGetId(array(

                    "name" => $request->name,
                    "image" => $file,
                    "supplier_id" => $request->user['supplier_id'],


                ));
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }


    public function ProductCategory(Request $request)
    {
        $brand = DB::table("product_brand")->where("supplier_id", $request->user['supplier_id'])->get();
        $data =   DB::table("product_category")->where("supplier_id", $request->user['supplier_id'])->get();
        return view("suppliers.product-category", compact("data", "brand"));
    }

    public function SaveProductCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:product_category,name',
            'seq' => 'required',
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
        $file = "";
        if ($request->hasFile('file')) {
            $file = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move('master images', $file);
        } else {
            if ($request->id) {
                $product_category =  DB::table("product_category")->where("id", $request->id)->first();
                $file = $product_category->image;
            }
        }

        try {
            if ($request->id) {
                DB::table('product_category')->where("id", $request->id)->update(array(
                    "name" => $request->name,
                    "seq" => $request->seq,
                    "image" => $file,
                    "supplier_id" => $request->user['supplier_id'],


                ));
            } else {
                DB::table('product_category')->insertGetId(array(
                    "name" => $request->name,
                    "seq" => $request->seq,
                    "image" => $file,
                    "supplier_id" => $request->user['supplier_id'],
                ));
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function ProductSubCategory(Request $request)
    {
        $category =   DB::table("product_category")->where("supplier_id", $request->user['supplier_id'])->get();

        $data =   DB::table("product_sub_category as a")
            ->select("a.*",   "b.name as category")
            ->join("product_category as b", "a.category_id", "b.id")
            ->where("a.supplier_id", $request->user['supplier_id'])->get();
        return view("suppliers.product-sub-category", compact("data", "category"));
    }

    public function GetProductCategory(Request $request)
    {
        return DB::table("product_category")->where("brand_id", $request->brand_id)->get();
    }

    public function SaveProductSubCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [


            'name' => 'required',
            'category_id' => 'required',


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
        $file = "";
        if ($request->hasFile('file')) {
            $file = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move('master images', $file);
        } else {
            if ($request->id) {
                $product_category =  DB::table("product_sub_category")->where("id", $request->id)->first();
                $file = $product_category->image;
            }
        }

        try {
            if ($request->id) {
                DB::table('product_sub_category')->where("id", $request->id)->update(array(

                    "name" => $request->name,
                    "category_id" => $request->category_id,
                    "image" => $file,
                    "supplier_id" => $request->user['supplier_id'],


                ));
            } else {
                DB::table('product_sub_category')->insertGetId(array(

                    "name" => $request->name,
                    "category_id" => $request->category_id,
                    "image" => $file,
                    "supplier_id" => $request->user['supplier_id'],


                ));
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function ProductSubSubCategory(Request $request)
    {
        $supplierId = $request->user['supplier_id'];

        $categories = DB::table("product_category")->where("supplier_id", $supplierId)->get();
        $subCategories = DB::table("product_sub_category")->where("supplier_id", $supplierId)->get();

        $data = DB::table("product_sub_sub_category as a")
            ->select("a.*", "b.name as category")
            ->join("product_category as b", "a.category_id", "b.id")
            ->where("a.supplier_id", $supplierId)
            ->get();

        return view("suppliers.product-sub-sub-category", compact("data", "categories", "subCategories"));
    }

    public function SaveProductSubSubCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'sub_category_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $fileName = "";
        if ($request->hasFile('file')) {
            $fileName = time() . '.' . $request->file('file')->extension();
            $request->file('file')->move(public_path('master_images'), $fileName);
        } else if ($request->id) {
            $existing = DB::table("product_sub_sub_category")->where("id", $request->id)->first();
            $fileName = $existing->image ?? "";
        }

        try {
            if ($request->id) {
                DB::table('product_sub_sub_category')->where("id", $request->id)->update([
                    "name" => $request->name,
                    "category_id" => $request->category_id,
                    "sub_category_id" => $request->sub_category_id,
                    "image" => $fileName,
                    "supplier_id" => $request->user['supplier_id'],
                ]);
            } else {
                DB::table('product_sub_sub_category')->insert([
                    "name" => $request->name,
                    "category_id" => $request->category_id,
                    "sub_category_id" => $request->sub_category_id,
                    "image" => $fileName,
                    "supplier_id" => $request->user['supplier_id'],
                ]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with("success", "Saved Successfully");
    }

    public function ProductUOM(Request $request)
    {

        $data =   DB::table("product_uom")->where("supplier_id", $request->user['supplier_id'])->get();
        return view("suppliers.product-uom", compact("data"));
    }

    public function SaveProductUOM(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
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
            if ($request->id) {
                DB::table('product_uom')->where("id", $request->id)->update(array(
                    "name" => $request->name,
                    "supplier_id" => $request->user['supplier_id'],
                ));
            } else {
                DB::table('product_uom')->insertGetId(array(
                    "name" => $request->name,
                    "supplier_id" => $request->user['supplier_id'],
                ));
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }
    public function ProductGST(Request $request)
    {

        $data =   DB::table("product_gst")->where("supplier_id", $request->user['supplier_id'])->get();
        return view("suppliers.product-gst", compact("data"));
    }

    public function SaveProductGST(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gst' => 'required',
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
            if ($request->id) {
                DB::table('product_gst')->where("id", $request->id)->update(array(
                    "gst" => $request->gst,
                    "supplier_id" => $request->user['supplier_id'],
                ));
            } else {
                DB::table('product_gst')->insertGetId(array(
                    "gst" => $request->gst,
                    "supplier_id" => $request->user['supplier_id'],
                ));
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function Products(Request $request)
    {
        $brand = DB::table("product_brand")->where("supplier_id", $request->user['supplier_id'])->get();
        $category = DB::table("product_category")->where("supplier_id", $request->user['supplier_id'])->get();
        $warehouse = WareHouse::where('is_active', 1)->get();
        $vendor = Vendor::where('active', 1)->get();
        $subCategories = DB::table("product_sub_category")->where("supplier_id",  $request->user['supplier_id'])->get();
        $product_uom = DB::table("product_uom")->where("supplier_id", $request->user['supplier_id'])->get();
        $gst = DB::table("product_gst")->where("supplier_id", $request->user['supplier_id'])->get();
        $query = DB::table("products as a")
            ->select("a.*", "b.name as brand", "c.name as category", "d.name as sub_category", "e.name as uom")
            ->LeftJoin("product_brand as b", "a.brand_id", "b.id")
            ->join("product_category as c", "a.category_id", "c.id")
            ->join("product_sub_category as d", "a.sub_category_id", "d.id")
            ->join("product_uom as e", "a.uom_id", "e.id")
            ->where("a.supplier_id", $request->user['supplier_id']);
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("a.name", "like", "%$search%")
                    ->orWhere("b.name", "like", "%$search%")
                    ->orWhere("a.article_no", "like", "%$search%");
            });
        }
        if (request("search_brand_id")) {
            $query->where("a.brand_id", request("search_brand_id"));
        }
        if (request("search_category_id")) {
            $query->where("a.category_id", request("search_category_id"));
        }
        $productCount = DB::table('products')->count();
        $productType = DB::table('product_type')->where('active', 1)->get();
        $data = $query->paginate(10)->withQueryString();
        return view("suppliers.products", compact("data", 'productType', 'warehouse', "vendor", "brand", "product_uom", "gst", "category", "productCount", "subCategories"));
    }


    public function UpdateBasePrice(Request $request)
    {
        DB::table('products')
            ->where('id', $request->id)
            ->where('supplier_id', $request->user['supplier_id'])
            ->update([
                'base_price' => $request->base_price
            ]);

        return response()->json(['status' => true]);
    }

    public function getProductAllocation(Request $request)
    {

        $data = DB::table('warehouse_product as wp')
            ->join('warehouse as w', 'w.id', '=', 'wp.warehouse_id')

            ->join('warehouse_location as wl', 'wl.id', '=', 'wp.warehouse_location_id')
            ->join('warehouse_zone as z', 'z.id', '=', 'wl.zone_id')
            ->join('products as p', 'p.id', '=', 'wp.product_id')
            ->where('wp.product_id', $request->product_id)
            ->select(
                'wp.id',
                'p.name as product_name',
                'w.name as warehouse_name',
                'z.zone_code',
                'wl.location_code'
            )
            ->get();
        return response()->json($data);
    }

    public function GetProductSubCategory(Request $request)
    {
        return DB::table("product_sub_category")
            ->where("category_id", $request->category_id)
            ->get();
    }

    public function GetProductSubSubCategory(Request $request)
    {
        return DB::table("product_sub_sub_category")
            ->where("sub_category_id", $request->sub_category_id)
            ->get();
    }

    private function generateTagsFromAI($productName)
    {
        $apiKey = config('services.openai.key');

        $prompt = "
                You are an Indian eCommerce SEO and marketing expert.  
                Write an SEO-optimized, consumer-friendly paragraph describing the product: \"$productName\" — explaining its uses, benefits, and relevance for Indian households.  

                Then, generate a list of 10–15 short tags that include:
                1. The product name in English.
                2. The most common local Indian names for this product used in daily speech (for example, 'Sugar' → 'Cheeni', 'Khaand', 'Sakkarai', 'Sakkare', 'Bellam', 'Misri', 'Shakkar',Sugar).
                3. Typical search keywords people might use to find it online.
                4. Include typical **search keywords and common misspellings** people might type online (for example: 'sugr', 'chini', 'cheni', 'cheenee', etc.).
                5. Ensure all tags are **auto-corrected** (no spelling errors).
                6.Avoid duplicates, irrelevant words, brand names, emojis, or special characters.
                Do not include translations that are not commonly used in India.
                Use simple, searchable names that real buyers would type.

                Return the result strictly in this JSON format:
                {
                \"description\": \"(A single descriptive paragraph about the product, 60–100 words)\",
                \"tags\": [\"tag1\", \"tag2\", \"tag3\", ...]
                }";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant that writes marketing-friendly product descriptions and tags.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);
        $aiData = $response->json();
        $description = '';
        $tags = [];

        if (isset($aiData['choices'][0]['message']['content'])) {
            $content = trim($aiData['choices'][0]['message']['content']);
            $parsed = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $description = $parsed['description'] ?? '';
                $tags = $parsed['tags'] ?? [];
            } else {
                $description = $content;
                $tags = [];
            }
        }

        return [
            'description' => $description,
            'tags' => $tags
        ];
    }


    public function generateMissingTagsBatch($console = null)
    {
        $products = DB::table('products')
            ->whereNull('tags')
            ->orWhere('tags', '')
            ->get();
        $total = count($products);
        $updated = 0;
        $skipped = 0;

        if ($console) {
            $console->info("🚀 Starting AI tag generation for {$total} products...\n");
        }
        foreach ($products as $index => $product) {
            if ($console) {
                $console->info("🧠 [" . ($index + 1) . "/{$total}.] Processing: {$product->name}");
            }
            $aiData = $this->generateTagsFromAI($product->name);
            if (!empty($aiData['description']) && !empty($aiData['tags'])) {
                $description = $aiData['description'];
                $tags = implode(',', $aiData['tags']);

                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'description' => $description,
                        'tags' => $tags,
                    ]);

                $updated++;

                if ($console) {
                    $console->info("✅ Updated: {$product->name}");
                }
            } else {
                $skipped++;
                if ($console) {
                    $console->warn("⚠️ Skipped: {$product->name}");
                }
            }
            sleep(1);
        }
        $summary = "🏁 Completed. Processed {$total} products → ✅ {$updated} updated, ⚠️ {$skipped} skipped.";

        if ($console) {
            $console->info("\n" . $summary);
        }
        return $summary;
    }

    // public function SaveProducts(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name'             => 'required',
    //         'category_id'      => 'required|integer|exists:product_category,id',
    //         'sub_category_id'  => 'required|integer|exists:product_sub_category,id',
    //         'base_price'       => 'required|numeric|min:0',
    //         'mrp'              => 'required|numeric|min:0',
    //         'gst'              => 'required|numeric|min:0|max:100',
    //         'uom_id'           => 'required|integer|exists:product_uom,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     try {
    //         $file = "";
    //         if ($request->hasFile('file')) {
    //             $file = time() . '.' . $request->file('file')->extension();
    //             $request->file('file')->move(public_path('product images'), $file);
    //         } else {
    //             if ($request->id) {
    //                 $product = DB::table("products")->where("id", $request->id)->first();
    //                 $file = $product->image ?? "";
    //             }
    //         }

    //         $tags = trim($request->tags ?? '');
    //         if (empty($tags)) {
    //             try {
    //                 $tags = $this->generateTagsFromAI($request->name);
    //             } catch (\Throwable $th) {
    //                 // AI failure shouldn't stop save
    //                 $tags = null;
    //             }
    //         }
    //         $description = trim($request->description ?? '');
    //         if (empty($description)) {
    //             try {
    //                 $description = $this->generateProductsDesc($request->name);
    //             } catch (\Throwable $th) {
    //                 // AI failure shouldn't stop save
    //                 $description = null;
    //             }
    //         }

    //         if ($request->id) {
    //             DB::table('products')->where("id", $request->id)->update([
    //                 "name"          => $request->name,
    //                 "image"         => $file,
    //                 "brand_id"      => $request->brand_id,
    //                 "category_id"   => $request->category_id,
    //                 "sub_category_id" => $request->sub_category_id,
    //                 "product_sub_sub_category" => $request->product_sub_sub_category,
    //                 "base_price"    => $request->base_price,
    //                 "mrp"           => $request->mrp,
    //                 "gst"           => $request->gst,
    //                 "cess_tax"      => $request->cess_tax,
    //                 "discount"      => $request->discount,
    //                 "article_no"    => $request->article_no,
    //                 "hsn_code"      => $request->hsn_code,
    //                 "uom_id"        => $request->uom_id,
    //                 "min_stock"     => $request->min_stock,
    //                 "description"   => $description,
    //                 "tags"          => $tags,
    //                 "supplier_id"   => $request->user['supplier_id'],
    //                 "video_link"    => $request->video_link,
    //                 "active"        => $request->active,
    //                 "qty"           => $request->qty,
    //             ]);
    //         } else {
    //             DB::table('products')->insertGetId([
    //                 "name"          => $request->name,
    //                 "image"         => $file,
    //                 "brand_id"      => $request->brand_id,
    //                 "category_id"   => $request->category_id,
    //                 "sub_category_id" => $request->sub_category_id,
    //                 "product_sub_sub_category" => $request->product_sub_sub_category,
    //                 "base_price"    => $request->base_price,
    //                 "mrp"           => $request->mrp,
    //                 "gst"           => $request->gst,
    //                 "cess_tax"      => $request->cess_tax,
    //                 "discount"      => $request->discount,
    //                 "article_no"    => $request->article_no,
    //                 "hsn_code"      => $request->hsn_code,
    //                 "uom_id"        => $request->uom_id,
    //                 "min_stock"     => $request->min_stock,
    //                 "description"   => $description,
    //                 "tags"          => $tags,
    //                 "supplier_id"   => $request->user['supplier_id'],
    //                 "video_link"    => $request->video_link,
    //                 "active"        => $request->active,
    //                 "qty"           => $request->qty,
    //             ]);
    //         }

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Product saved successfully'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function SaveProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('products', 'name')->ignore($request->id),
            ],
            'category_id'      => 'required|integer|exists:product_category,id',
            'sub_category_id'  => 'required|integer|exists:product_sub_category,id',
            'base_price'       => 'required|numeric|min:0',
            // 'mrp'              => 'required|numeric|min:0',
            'gst'              => 'required|numeric|min:0|max:100',
            'uom_id'           => 'required|integer|exists:product_uom,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        try {
            $file = "";
            if ($request->hasFile('file')) {
                $file = time() . '.' . $request->file('file')->extension();
                $request->file('file')->move(public_path('product images'), $file);
            } else {
                if ($request->id) {
                    $product = DB::table("products")->where("id", $request->id)->first();
                    $file = $product->image ?? "";
                }
            }


            $description = '';
            $tags = '';

            if (!$request->id) {
                $apiKey = config('services.openai.key');
                $productName = $request->name;
                $prompt = "Write SEO description and tags for product \"$productName\" in JSON format.";
                $gptResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);
                $aiData = $gptResponse->json();
                if (isset($aiData['choices'][0]['message']['content'])) {
                    $parsed = json_decode($aiData['choices'][0]['message']['content'], true);
                    $description = $parsed['description'] ?? '';
                    $tags = isset($parsed['tags']) ? implode(',', $parsed['tags']) : '';
                }
            } else {
                $oldProduct = DB::table("products")->where("id", $request->id)->first();
                $description = $request->description ?? '';
                // $description = $request->description
                //     ? $request->description
                //     : ($oldProduct->description ?? '');
                $tags = $request->tags
                    ? $request->tags
                    : ($oldProduct->tags ?? '');
            }
            $productData = [
                "name"          => $request->name,
                "image"         => $file,
                "brand_id"      => $request->brand_id,
                "product_type_id"      => $request->product_type_id,
                "category_id"   => $request->category_id,
                "sub_category_id" => $request->sub_category_id,
                "product_sub_sub_category" => $request->product_sub_sub_category,
                "base_price"    => $request->base_price,
                "mrp"           => $request->filled('mrp') ? $request->mrp : 0.00,
                "gst"           => $request->gst,
                "cess_tax"      => $request->cess_tax,
                "discount"      => $request->filled('discount') ? $request->discount : 0.00,
                "hsn_code"      => $request->hsn_code,
                "uom_id"        => $request->uom_id,
                "per_uom"        => $request->per_uom,
                "min_stock"     => $request->min_stock,
                "description"   => $description,
                "tags"          => $tags,
                "supplier_id"   => $request->user['supplier_id'],
                "video_link"    => $request->video_link,
                "active"        => $request->active,
                "qty"           => $request->filled('qty') ? $request->qty : 1,
            ];

            if ($request->id) {
                DB::table('products')
                    ->where("id", $request->id)
                    ->update($productData);
            } else {
                $product_id = "";
                DB::transaction(function () use (&$productData) {
                    $lastProduct = DB::table('products')
                        ->lockForUpdate()
                        ->orderBy('article_no', 'desc')
                        ->first();
                    if ($lastProduct && $lastProduct->article_no) {
                        $newArticleNo = $lastProduct->article_no + 1;
                    } else {
                        $newArticleNo = 10000;
                    }
                    $productData['article_no'] = $newArticleNo;
                });
                $product_id =  DB::table('products')->insertGetId($productData);


                $vendor = DB::table("vendor")->get();
                foreach ($vendor as $key => $value) {
                    DB::table("vendor_products")->insert(array(
                        "supplier_id" => $request->user["supplier_id"],
                        "product_id" => $product_id,
                        "vendor_id" => $value->id,
                    ));
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'Product saved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function searchProduct(Request $request)
    {
        $search = $request->q;
        $search = preg_replace('/[^A-Za-z0-9 ]/', '', $search);
        $products = DB::table('products')
            ->where('active', 1)
            ->whereRaw("REPLACE(REPLACE(REPLACE(name, '-', ''), '.', ''), '  ', ' ') LIKE ?", ["%{$search}%"])
            ->limit(100)
            ->get();
        return response()->json($products);
    }

    public function uploadMultipleImages(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id'    => 'required',
            'files' => 'required',


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
        $filename = "";


        try {

            $files = $request->file('files');
            if ($files && is_array($files)) {
                foreach ($files as $fileItem) {
                    if ($fileItem && $fileItem->isValid()) {
                        $filename = time() . '_' . uniqid() . '.' . $fileItem->extension();
                        $fileItem->move(public_path('product images'), $filename);

                        DB::table('product_images')->insert([
                            "product_id" => $request->id,
                            "image" => $filename,
                        ]);
                    }
                }
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function Documents(Request $request)
    {
        $data = DB::table("documents")->get();
        return view("admin.documents", compact("data"));
    }

    public function SaveDocuments(Request $request)
    {
        $validator = Validator::make($request->all(), [


            'name' => 'required',
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
            if ($request->id) {
                DB::table('documents')->where("id", $request->id)->update(array(
                    "name" => $request->name,
                    "type" => $request->type,
                ));
            } else {
                DB::table('documents')->insertGetId(array(
                    "name" => $request->name,
                    "type" => $request->type,
                ));
            }
        } catch (Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }

        return  redirect()->back()->with("success", "Save Successfully");
    }

    public function UpdateProductStatus(Request $request)
    {
        DB::table("products")->where("id", $request->id)->update(array("active" => $request->active));
    }

    public function UpdateProductIsdeal(Request $request)
    {
        DB::table("products")->where("id", $request->id)->update(array("is_deal" => $request->is_deal));
    }

    public function UpdateProductDiscount(Request $request)
    {
        DB::table("products")->where("id", $request->id)->update(array("is_discount" => $request->is_discount));
    }


    public function UpdateProductIsHome(Request $request)
    {
        DB::table("products")->where("id", $request->id)->update(array("is_home" => $request->is_home));
    }

    public function expenseCategory(Request $request)
    {
        $data =   DB::table("supplier_expense_category")->where("supplier_id", $request->user['supplier_id'])->get();
        return view("suppliers.supplier-expense-category", compact("data"));
    }

    public function expenseSaveCategory(Request $request)
    { {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
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
                if ($request->id) {
                    DB::table('supplier_expense_category')->where("id", $request->id)->update(array(
                        "name" => $request->name,
                        "supplier_id" => $request->user['supplier_id'],
                    ));
                } else {
                    DB::table('supplier_expense_category')->insertGetId(array(
                        "name" => $request->name,
                        "supplier_id" => $request->user['supplier_id'],
                    ));
                }
            } catch (Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
            return  redirect()->back()->with("success", "Save Successfully");
        }
    }

    public function expenseSubCategory(Request $request)
    {
        $supplierId = $request->user['supplier_id'];

        $data = DB::table('supplier_expense_subcategory as sub')
            ->leftJoin('supplier_expense_category as cat', 'sub.expense_cat_id', '=', 'cat.id')
            ->select('sub.*', 'cat.name as expense_category')
            ->where('sub.supplier_id', $supplierId)
            ->get();

        $supplierExpCat = DB::table('supplier_expense_category')
            ->where('supplier_id', $supplierId)
            ->get();

        return view('suppliers.supplier-expense-sub-category', compact('data', 'supplierExpCat'));
    }

    public function expenseSaveSubCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'expense_cat_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            $data = [
                'name' => $request->name,
                'expense_cat_id' => $request->expense_cat_id,
                'supplier_id' => $request->user['supplier_id'],
            ];

            if ($request->id) {
                DB::table('supplier_expense_subcategory')
                    ->where('id', $request->id)
                    ->update($data);
            } else {
                DB::table('supplier_expense_subcategory')->insert($data);
            }

            return redirect()->back()->with('success', 'Saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function expenseList(Request $request)
    {
        $supplierId = $request->user['supplier_id'];

        $data = DB::table('supplier_expense as e')
            ->leftJoin('supplier_expense_category as c', 'e.expense_cat_id', '=', 'c.id')
            ->leftJoin('supplier_expense_subcategory as s', 'e.expense_subcat_id', '=', 's.id')
            ->select('e.*', 'c.name as expense_category', 's.name as expense_subcategory')
            ->where('e.supplier_id', $supplierId)
            ->orderByDesc('e.id')
            ->get();

        $supplierExpCat = DB::table('supplier_expense_category')
            ->where('supplier_id', $supplierId)
            ->get();

        $supplierExpSubCat = DB::table('supplier_expense_subcategory')
            ->where('supplier_id', $supplierId)
            ->get();

        return view('suppliers.supplier-expense-list', compact('data', 'supplierExpCat', 'supplierExpSubCat'));
    }


    public function expenseSave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expense_cat_id'    => 'required|integer',
            'expense_subcat_id' => 'required|integer',
            'name'              => 'required|string|max:255',
            'amount'            => 'required|numeric|min:0',
            'expense_date'      => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            $data = [
                'expense_cat_id'    => $request->expense_cat_id,
                'expense_subcat_id' => $request->expense_subcat_id,
                'name'              => $request->name,
                'amount'            => $request->amount,
                'expense_date'      => $request->expense_date,
                'note'              => $request->note,
                'supplier_id'       => $request->user['supplier_id'],
            ];
            if ($request->id) {
                DB::table('supplier_expense')
                    ->where('id', $request->id)
                    ->update($data);
            } else {
                $catName = DB::table('supplier_expense_category')
                    ->where('id', $request->expense_cat_id)
                    ->value('name');
                $subCatName = DB::table('supplier_expense_subcategory')
                    ->where('id', $request->expense_subcat_id)
                    ->value('name');
                $data['ex_cat_name'] = $catName;
                $data['ex_sub_cat_name'] = $subCatName;
                DB::table('supplier_expense')->insert($data);
            }
            return redirect()->back()->with('success', 'Expense saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function SaveProductCategoryAjax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:product_category,name',
            'seq' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {

            $file = "";

            if ($request->hasFile('file')) {
                $file = time() . '.' . $request->file('file')->extension();
                $request->file('file')->move(public_path('master_images'), $file);
            }

            if ($request->id) {

                DB::table('product_category')
                    ->where("id", $request->id)
                    ->update([
                        "name" => $request->name,
                        "seq" => $request->seq,
                        "image" => $file,
                        "supplier_id" => $request->user['supplier_id'],
                    ]);

                $id = $request->id;
            } else {

                $id = DB::table('product_category')->insertGetId([
                    "name" => $request->name,
                    "seq" => $request->seq,
                    "image" => $file,
                    "supplier_id" => $request->user['supplier_id'],
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Saved Successfully',
                'id' => $id
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCategories(Request $request)
    {
        $data = DB::table('product_category')
            ->where('supplier_id', $request->user['supplier_id'])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function SaveProductBrandAjax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {

            $file = "";

            if ($request->hasFile('file')) {
                $file = time() . '.' . $request->file('file')->extension();
                $request->file('file')->move(public_path('master_images'), $file);
            }

            $id = DB::table('product_brand')->insertGetId([
                "name" => $request->name,
                "image" => $file,
                "supplier_id" => $request->user['supplier_id'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Brand Saved Successfully',
                'id' => $id
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getBrandsAjax(Request $request)
    {
        $data = DB::table('product_brand')
            ->where('supplier_id', $request->user['supplier_id'])
            ->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function SaveProductSubCategoryAjax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        try {
            $file = "";
            if ($request->hasFile('file')) {
                $file = time() . '.' . $request->file('file')->extension();
                $request->file('file')->move(public_path('master_images'), $file);
            }
            $id = DB::table('product_sub_category')->insertGetId([
                "name" => $request->name,
                "category_id" => $request->category_id,
                "image" => $file,
                "supplier_id" => $request->user['supplier_id'],
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Sub Category Saved Successfully',
                'id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getSubCategoriesAjax(Request $request)
    {
        $data = DB::table('product_sub_category')
            ->where('supplier_id', $request->user['supplier_id'])
            ->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function SaveProductSubSubCategoryAjax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'sub_category_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {

            $fileName = "";

            if ($request->hasFile('file')) {
                $fileName = time() . '.' . $request->file('file')->extension();
                $request->file('file')->move(public_path('master_images'), $fileName);
            }

            $id = DB::table('product_sub_sub_category')->insertGetId([
                "name" => $request->name,
                "category_id" => $request->category_id,
                "sub_category_id" => $request->sub_category_id,
                "image" => $fileName,
                "supplier_id" => $request->user['supplier_id'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Sub Sub Category Saved Successfully',
                'id' => $id
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getSubCategoriesByCategoryAjax(Request $request)
    {
        $data = DB::table('product_sub_category')
            ->where('category_id', $request->category_id)
            ->where('supplier_id', $request->user['supplier_id'])
            ->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getSubSubCategoriesAjax()
    {
        $data = DB::table('product_sub_sub_category')->get();
        return response()->json([
            'data' => $data
        ]);
    }
}
