<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Products;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithChunkReading
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $supplier_id;

    public function __construct($supplier_id)
    {
        $this->supplier_id = $supplier_id;
    }

    public function collection()
    {
        return DB::table("products as a")
            ->select(
                "a.id",
                "a.name",
                "a.base_price",
                "b.name as brand",
                "c.name as category",
                "d.name as sub_category",
                "e.name as uom",
                "a.discount",
                "a.gst",
                "a.mrp",
                "a.cess_tax",
                "a.description",
                "a.article_no",
                "a.hsn_code",
            )
            ->leftJoin("product_brand as b", "a.brand_id", "b.id")
            ->join("product_category as c", "a.category_id", "c.id")
            ->join("product_sub_category as d", "a.sub_category_id", "d.id")
            ->join("product_uom as e", "a.uom_id", "e.id")
            ->where("a.supplier_id", $this->supplier_id)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Product Name',
            'Base Price',
            'Brand',
            'Category',
            'Sub Category',
            'UOM',
            'Discount',
            'GST',
            'MRP',
            'Cess Tax',
            'Description',
            'Article No',
            'HSN Code',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
