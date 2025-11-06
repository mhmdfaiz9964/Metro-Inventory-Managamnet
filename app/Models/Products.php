<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'product_category_id', 'supplier_id', 'status', 'image', 'description', 'regular_price', 'wholesale_price', 'sale_price', 'warranty', 'weight', 'product_brand_id', 'model', 'is_manufactured'];

    /**
     * Each product belongs to a category
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * Each product may have one stock record
     */
    public function stock()
    {
        return $this->hasOne(Stocks::class, 'product_id');
    }

    /**
     * Supplier relation
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function bomComponents()
    {
        return $this->hasMany(BomComponent::class, 'product_id');
    }

    public function brand()
    {
        return $this->belongsTo(ProductBrand::class, 'product_brand_id');
    }
}
