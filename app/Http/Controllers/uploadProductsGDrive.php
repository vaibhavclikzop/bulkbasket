<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;

class uploadProductsGDrive extends Controller
{
    // private function getGoogleDriveImages($folderId)
    // {
    //     $apiKey = env('GOOGLE_DRIVE_API_KEY');
    //     $url = "https://www.googleapis.com/drive/v3/files?q='$folderId'+in+parents&key=$apiKey&fields=files(id,name,mimeType)";

    //     $response = Http::get($url);
    //     $files = $response->json()['files'] ?? [];

    //     $imageUrls = [];

    //     foreach ($files as $file) {
    //         if (strpos($file['mimeType'], 'image') !== false) {
    //             $imageUrls[] = "https://drive.usercontent.google.com/download?id=" . $file['id'] . "&authuser=1";
    //         }
    //     }

    //     return $imageUrls; // Returns an array of direct image URLs

    // }
    // private function downloadImage($imageUrl)
    // {
    //     $response = Http::get($imageUrl);

    //     if (!$response->successful()) {
    //         throw new Exception("Failed to download image from Google Drive.");
    //     }

    //     $imageName = time() . '.jpg';
    //     $imagePath = public_path('product_image/' . $imageName);

    //     // Save the image in the 'public/product_image' directory
    //     file_put_contents($imagePath, $response->body());

    //     return $imageName;
    // }


    private function downloadImageFromDriveLink($sharedLink)
    {
        // Extract File ID from shared link
        if (!preg_match('/\/file\/d\/(.*?)\//', $sharedLink, $matches)) {
            throw new Exception("Invalid Google Drive link.");
        }

        $fileId = $matches[1];

        // Convert to direct download URL
        $downloadUrl = "https://drive.google.com/uc?export=download&id=" . $fileId;

        // Download the file
        $response = Http::get($downloadUrl);

        if (!$response->successful()) {
            throw new Exception("Failed to download image from Google Drive.");
        }

        // Generate image name and path
        $imageName = time() . '.jpg';
        $imagePath = public_path('product images/' . $imageName);

        // Save the image to local path
        file_put_contents($imagePath, $response->body());

        return $imageName;
    }



    public function importGDriveProducts(Request $request)
    {
        // Validate uploaded file
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with("error", $validator->errors()->first());
        }

        if (!$request->hasFile('file')) {
            return redirect()->back()->with("error", "No file uploaded.");
        }

        try {
            // Store uploaded CSV file
            $file = $request->file('file');
            $filePath = $file->store('csv', 'public');
            $csv = \League\Csv\Reader::createFromPath(storage_path('app/public/' . $filePath), 'r');
            $csv->setHeaderOffset(0); // Treat first row as header

            $duplicate = 0;
            $error = "";
            $error_count = 0;
            $success = 0;
            $count = 1;

            $supplier_id = $request->user['supplier_id'] ?? ($request->user()->supplier_id ?? null);

            foreach ($csv as $record) {
                try {
                    // Skip empty rows
                    if (empty(trim($record['Name'] ?? ''))) {
                        continue;
                    }

                    // --- Brand ---
                    $brand_id = null;
                    if (!empty($record['Brand'])) {
                        $brand_name = trim($record['Brand']);
                        $brand = DB::table("product_brand")
                            ->where("name", $brand_name)
                            ->where("supplier_id", $supplier_id)
                            ->first();

                        $brand_id = $brand ? $brand->id : DB::table('product_brand')->insertGetId([
                            "name" => $brand_name,
                            "supplier_id" => $supplier_id,
                        ]);
                    }

                    // --- Category ---
                    $category_id = null;
                    if (!empty($record['Category'])) {
                        $category_name = trim($record['Category']);
                        $category = DB::table("product_category")
                            ->where("name", $category_name)
                            ->where("supplier_id", $supplier_id)
                            ->first();

                        $category_id = $category ? $category->id : DB::table('product_category')->insertGetId([
                            "name" => $category_name,
                            "supplier_id" => $supplier_id,
                        ]);
                    }

                    // --- Sub Category ---
                    $sub_category_id = null;
                    if (!empty($record['SubCategory'])) {
                        $sub_category_name = trim($record['SubCategory']);
                        $sub_category = DB::table("product_sub_category")
                            ->where("name", $sub_category_name)
                            ->where("supplier_id", $supplier_id)
                            ->first();

                        $sub_category_id = $sub_category ? $sub_category->id : DB::table('product_sub_category')->insertGetId([
                            "name" => $sub_category_name,
                            "category_id" => $category_id,
                            "supplier_id" => $supplier_id,
                        ]);
                    }

                    // --- Sub Sub Category (optional) ---
                    $sub_sub_category_id = null;
                    if (!empty($record['SubSubCategory'])) {
                        $ssc_name = trim($record['SubSubCategory']);
                        $sub_sub_category = DB::table("product_sub_sub_category")
                            ->where("name", $ssc_name)
                            ->where("supplier_id", $supplier_id)
                            ->first();

                        $sub_sub_category_id = $sub_sub_category ? $sub_sub_category->id : DB::table('product_sub_sub_category')->insertGetId([
                            "name" => $ssc_name,
                            "category_id" => $category_id,
                            "sub_category_id" => $sub_category_id,
                            "supplier_id" => $supplier_id,
                        ]);
                    }

                    // --- Product Name ---
                    $productName = trim($record['Name'] ?? '');
                    if (empty($productName)) {
                        $error .= "Row $count: Product name missing.<br>";
                        $error_count++;
                        $count++;
                        continue;
                    }

                    // --- Check Duplicate ---
                    $existingProduct = DB::table("products")
                        ->where("name", $productName)
                        ->where("supplier_id", $supplier_id)
                        ->first();

                    if ($existingProduct) {
                        $error .= "Row $count: Duplicate product - $productName.<br>";
                        $error_count++;
                        $duplicate++;
                        $count++;
                        continue;
                    }

                    // --- UOM ---
                    $unit_type_id = null;
                    if (!empty($record['UOM'])) {
                        $unit_name = trim($record['UOM']);
                        $unit_type = DB::table("product_uom")
                            ->where("name", $unit_name)
                            ->where("supplier_id", $supplier_id)
                            ->first();

                        $unit_type_id = $unit_type ? $unit_type->id : DB::table('product_uom')->insertGetId([
                            "name" => $unit_name,
                            "supplier_id" => $supplier_id,
                        ]);
                    }

                    // --- Insert Product ---
                    DB::table('products')->insert([
                        "brand_id" => $brand_id,
                        "category_id" => $category_id,
                        "sub_category_id" => $sub_category_id,
                        "product_sub_sub_category" => $sub_sub_category_id,
                        "name" => $productName,
                        "base_price" => $record['BasePrice'] ?? 0,
                        "mrp" => $record['MRP'] ?? 0,
                        "gst" => $record['GST'] ?? 0,
                        "cess_tax" => $record['Cess'] ?? 0,
                        "article_no" => $record['Article'] ?? null,
                        "hsn_code" => $record['HSN'] ?? null,
                        "uom_id" => $unit_type_id,
                        "min_stock" => $record['MinStock'] ?? 0,
                        "description" => $record['Description'] ?? null,
                        "supplier_id" => $supplier_id,
                        "temp_image" => $record['TempImage'] ?? null,
                        "active" => 0,
                    ]);

                    $success++;
                } catch (\Throwable $th) {
                    $error .= "Row $count: Error - " . $th->getMessage() . "<br>";
                    $error_count++;
                }

                $count++;
            }

            return redirect()->back()->with(
                "success",
                "Import completed. Total: " . ($count - 1) .
                    " | Success: $success | Duplicates: $duplicate | Errors: $error_count"
            )->with("msg", $error);
        } catch (\Throwable $th) {
            return redirect()->back()->with("error", "Failed to process CSV: " . $th->getMessage());
        }
    }
}
