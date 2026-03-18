<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseProductsLocation extends Model
{
    protected $primaryKey = "id";
    protected $table = "warehouse_product";

    protected $fillable = [
        'warehouse_id',
        'warehouse_location_id',
        'product_id',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}
