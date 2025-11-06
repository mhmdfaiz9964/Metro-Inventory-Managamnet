<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseManufacturedProductItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_manufactured_product_id',
        'product_id',
        'cost_price',
        'qty',
        'discount_type',
        'discount',
    ];

    public function purchase()
    {
        return $this->belongsTo(PurchaseManufacturedProduct::class, 'purchase_manufactured_product_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}
