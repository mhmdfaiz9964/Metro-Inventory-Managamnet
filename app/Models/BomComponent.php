<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'price',
        'required_bom_qty',
        'quantity',  
        'qty_alert',
        'notes',
        'product_code',
        'brand_id',  // make sure this is fillable
        'model',     // also add model if needed
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function stock()
    {
        return $this->hasOne(BomStock::class, 'bom_component_id');
    }

    public function manufactureItems()
    {
        return $this->hasMany(ManufactureItem::class, 'bom_component_id');
    }
}
