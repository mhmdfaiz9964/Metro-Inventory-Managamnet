<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBrand extends Model
{
    use HasFactory;

    protected $table = 'product_brands';

    protected $fillable = [
        'name',
        'note',
    ];

    // Relationship: one brand has many products
    public function products()
    {
        return $this->hasMany(Products::class, 'product_brand_id');
    }
}
