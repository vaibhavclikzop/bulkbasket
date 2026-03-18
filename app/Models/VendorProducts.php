<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProducts extends Model
{
    protected $primaryKey = "id";
    protected $table = "vendor_products";
    protected $fillable = [
        'supplier_id',
        'vendor_id',
        'product_id'
    ];
}
