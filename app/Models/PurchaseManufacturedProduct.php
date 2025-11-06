<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseManufacturedProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufactured_product',
        'total_price',
        'discount_type',
        'discount',
        'supplier_id',
        'date',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseManufacturedProductItem::class, 'purchase_manufactured_product_id');
    }

    public function payments()
    {
        return $this->hasMany(PurchaseManufacturedProductPayment::class, 'purchase_manufactured_product_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
