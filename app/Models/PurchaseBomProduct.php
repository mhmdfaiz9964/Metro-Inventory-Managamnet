<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseBomProduct extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_id', 'bom_id', 'qty', 'cost_price', 'discount', 'discount_type'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function bomComponent()
    {
        return $this->belongsTo(BomComponent::class, 'bom_id');
    }
}
