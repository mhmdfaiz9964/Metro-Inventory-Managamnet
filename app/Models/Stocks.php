<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stocks extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'available_stock',
        'stock_alert',
        'notes'
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
