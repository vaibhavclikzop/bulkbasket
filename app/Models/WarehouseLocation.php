<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseLocation extends Model
{
    protected $primaryKey = "id";
    protected $table = "warehouse_location";
    protected $fillable = [
        'warehouse_id',
        'zone_id',
        'row',
        'rack',
        'shelf',
        'bin',
        'location_code',
        'store'
    ];

    public function warehouse()
    {
        return $this->belongsTo(WareHouse::class, 'warehouse_id', 'id');
    }

    public function warehouseZone()
    {
        return $this->belongsTo(warehouseZone::class, 'zone_id', 'id');
    }
}
