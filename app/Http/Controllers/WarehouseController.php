<?php

namespace App\Http\Controllers;

use App\Models\WareHouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseProductsLocation;
use App\Models\WareHouseZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Picqer\Barcode\BarcodeGeneratorPNG;

class WarehouseController extends Controller
{

    public function wareHouseZone()
    {
        $warehouseZone = WareHouseZone::all();
        $data = compact('warehouseZone');
        return view('suppliers.warehouse-zone')->with($data);
    }

    public function updateWareHouseZone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'zone_code' => 'required',
        ]);


        if ($request->id) {
            DB::table('warehouse_zone')->where('id', $request->id)->update([
                'area' => $request->area,
                'zone_code' => $request->zone_code,
                'description' => $request->description,
                'is_active' => $request->is_active,
            ]);
            return redirect()->back()->with('success', 'Ware House Zone Updated Successfully');
        } else {
            DB::table('warehouse_zone')->insert([
                'area' => $request->area,
                'zone_code' => $request->zone_code,
                'description' => $request->description,
                'is_active' => $request->is_active,
            ]);
            return redirect()->back()->with('success', 'Ware House Zone Added Successfully');
        }
    }


    public function warehouseList(Request $request)
    {
        $warehouse = WareHouse::all();
        $data = compact('warehouse');
        return view('suppliers.ware-house')->with($data);
    }

    public function saveWareHouse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'code' => 'required|unique:warehouse,code,' . $request->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Ware House Code Unique');
        }
        if ($request->id) {
            DB::table('warehouse')->where('id', $request->id)->update([
                'name' => $request->name,
                'code' => $request->code,
                'country' => $request->country,
                'address' => $request->address,
                'state' => $request->state,
                'city' => $request->city,
                'pincode' => $request->pincode,
                'is_active' => $request->is_active,
            ]);
            return redirect()->back()->with('success', 'Ware House Updated Successfully');
        } else {
            DB::table('warehouse')->insert([
                'name' => $request->name,
                'code' => $request->code,
                'country' => $request->country,
                'address' => $request->address,
                'state' => $request->state,
                'city' => $request->city,
                'pincode' => $request->pincode,
                'is_active' => $request->is_active,
            ]);
            return redirect()->back()->with('success', 'Ware House Added Successfully');
        }
    }

    public function warehouseLocation(Request $request, $id)
    {
        $warehouseName = WareHouse::where('id', $id)->first();
        $warehouseZone = WareHouseZone::where('is_active', 1)->get();
        $query = WarehouseLocation::with('warehouse', 'warehouseZone')
            ->where('warehouse_id', $id);
        if ($request->zone_category) {
            $query->where('zone_id', $request->zone_category);
        }
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('row', 'LIKE', "%$search%")
                    ->orWhere('rack', 'LIKE', "%$search%")
                    ->orWhere('shelf', 'LIKE', "%$search%")
                    ->orWhere('bin', 'LIKE', "%$search%")
                    ->orWhere('location_code', 'LIKE', "%$search%");
            });
        }
        $warehouseLocation = $query->paginate(50)->withQueryString();
        return view('suppliers.ware-house-location', compact(
            'warehouseLocation',
            'warehouseName',
            'warehouseZone'
        ));
    }

    public function saveWareHouseLocation(Request $request)
    {
        if ($request->id) {
            $location = WarehouseLocation::find($request->id);
            if (!$location) {
                return redirect()->back()->with('error', 'Location not found');
            }
        } else {
            $location = new WarehouseLocation();
            $location->warehouse_id = $request->warehouse_id;
        }
        $zone = WareHouseZone::find($request->zone_id);
        if (!$zone) {
            return redirect()->back()->with('error', 'Zone not found');
        }
        $location->zone_id = $request->zone_id;
        $location->row = $request->row;
        $location->rack = $request->rack;
        $location->shelf = $request->shelf;
        $location->bin = $request->bin;
        $location->store = $request->store;
        $location->is_active = $request->is_active;
        $locationParts = [
            $zone->zone_code,
            $request->row,
            $request->rack,
            $request->shelf,
            $request->bin
        ];
        if (!empty($request->store)) {
            $locationParts[] = $request->store;
        }
        $locationCode = implode('-', $locationParts);
        $location->location_code = $locationCode;
        // $barcodeFileName = $locationCode . '.png';
        // $barcodePath = public_path('warehouse-slab-barcode/' . $barcodeFileName);
        // if (!file_exists(public_path('warehouse-slab-barcode'))) {
        //     mkdir(public_path('warehouse-slab-barcode'), 0777, true);
        // }
        // $generator = new BarcodeGeneratorPNG();
        // $barcodeData = $generator->getBarcode(
        //     $locationCode,
        //     $generator::TYPE_CODE_128,
        //     2,
        //     60
        // );
        // $barcodeFileName = $locationCode . '.png';
        // $folderPath = public_path('warehouse-slab-barcode');
        // if (!file_exists($folderPath)) {
        //     mkdir($folderPath, 0777, true);
        // }
        // $barcodePath = $folderPath . '/' . $barcodeFileName;
        // file_put_contents($barcodePath, $barcodeData);
        // $location->bar_code = 'warehouse-slab-barcode/' . $barcodeFileName;
        $location->save();
        return redirect()->back()->with(
            'success',
            $request->id ? 'Location Updated Successfully' : 'Location Added Successfully'
        );
    }


    public function importWareHouseLocation(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);
        session(['import_progress' => 0]);
        $file = fopen($request->file('file')->getRealPath(), 'r');
        $totalRows = count(file($request->file('file')));
        $success = 0;
        $failed = 0;
        $processed = 0;
        DB::beginTransaction();
        try {

            while (($row = fgetcsv($file)) !== false) {

                $processed++;

                // Skip header
                if ($processed == 1 && strtolower(trim($row[0])) == 'warehouse_name') {
                    continue;
                }

                // 1️⃣ Warehouse
                $warehouse = WareHouse::where('name', trim($row[0]))->first();
                if (!$warehouse) {
                    $failed++;
                    continue;
                }

                // 2️⃣ Zone by zone_code
                $zone = WareHouseZone::where('zone_code', trim($row[1]))->first();
                if (!$zone) {
                    $failed++;
                    continue;
                }
                $parts = [
                    $zone->zone_code,
                    trim($row[2]),
                    trim($row[3]),
                    trim($row[4]),
                    trim($row[5]),
                ];

                if (!empty(trim($row[6]))) {
                    $parts[] = trim($row[6]);
                }
                $locationCode = implode('-', $parts);
                // $barcodeFileName = $locationCode . '.png';
                // $barcodePath = public_path('warehouse-slab-barcode/' . $barcodeFileName);
                // if (!file_exists(public_path('warehouse-slab-barcode'))) {
                //     mkdir(public_path('warehouse-slab-barcode'), 0777, true);
                // }
                // $generator = new BarcodeGeneratorPNG();
                // $barcodeData = $generator->getBarcode(
                //     $locationCode,
                //     $generator::TYPE_CODE_128,
                //     2,
                //     60
                // );
                // $barcodeFileName = $locationCode . '.png';
                // $folderPath = public_path('warehouse-slab-barcode');
                // if (!file_exists($folderPath)) {
                //     mkdir($folderPath, 0777, true);
                // }
                // $barcodePath = $folderPath . '/' . $barcodeFileName;
                // file_put_contents($barcodePath, $barcodeData);
                // $barcodeLocation = 'warehouse-slab-barcode/' . $barcodeFileName;
                WarehouseLocation::firstOrCreate(
                    [
                        'warehouse_id' => $warehouse->id,
                        'zone_id' => $zone->id,
                        'row' => trim($row[2]),
                        'rack' => trim($row[3]),
                        'shelf' => trim($row[4] ?? ''),
                        'bin' => trim($row[5] ?? ''),
                        'store' => trim($row[6] ?? ''),
                    ],
                    [
                        'location_code' => $locationCode
                        // 'bar_code' => $barcodeLocation
                    ]
                );
                $success++;

                session([
                    'import_progress' => intval(($processed / $totalRows) * 100)
                ]);
            }

            fclose($file);
            DB::commit();

            session(['import_progress' => 100]);

            return response()->json([
                'status' => 'success',
                'success_count' => $success,
                'failed_count' => $failed,
                'message' => "Import Completed ✅ | Success: $success | Failed: $failed"
            ]);
        } catch (\Exception $e) {

            DB::rollBack();
            session(['import_progress' => 0]);

            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function wareHouseProductLocation(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required',
            'warehouse_location_id' => 'required',
            'product_id' => 'array'
        ]);
        $warehouseId = $request->warehouse_id;
        $locationId  = $request->warehouse_location_id;
        $productIds  = $request->product_id ?? [];
        WarehouseProductsLocation::where('warehouse_id', $warehouseId)
            ->where('warehouse_location_id', $locationId)
            ->whereNotIn('product_id', $productIds)
            ->delete();
        foreach ($productIds as $productId) {
            WarehouseProductsLocation::updateOrCreate([
                'warehouse_id' => $warehouseId,
                'warehouse_location_id' => $locationId,
                'product_id' => $productId
            ]);
        }

        return back()->with('success', 'Products updated successfully');
    }

    public function getAllocatedProducts(Request $request)
    {
        $products = WarehouseProductsLocation::with('product')
            ->where('warehouse_id', $request->warehouse_id)
            ->where('warehouse_location_id', $request->warehouse_location_id)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->product_id,
                    'name' => $row->product->name
                ];
            });

        return response()->json($products);
    }

    public function warehouseLocationPending(Request $request, $id)
    {
        $warehouseName = WareHouse::where('id', $id)->first();
        $allocatedProductIds = WarehouseProductsLocation::where('warehouse_id', $id)
            ->pluck('product_id')
            ->toArray();
        $pendingProducts = DB::table('products as p')
            // ->leftJoin('product_brand as b', 'p.brand_id', '=', 'b.id')
            ->leftJoin('product_category as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('product_sub_category as s', 'p.sub_category_id', '=', 's.id')
            ->where('p.active', 1)
            ->whereNotIn('p.id', $allocatedProductIds)
            ->select(
                'p.*',
                // 'b.name as brand_name',
                'c.name as category_name',
                's.name as subcategory_name'
            )
            ->get();
        $data = compact('pendingProducts', 'warehouseName');
        return view('suppliers.warehouse-product-pending')->with($data);
    }
}
