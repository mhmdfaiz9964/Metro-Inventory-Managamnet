<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = ['stock_id', 'adjustment_type', 'quantity', 'reason_type', 'adjusted_by'];

    /**
     * Relationship to Stock
     */
    public function stock()
    {
        return $this->belongsTo(Stocks::class, 'stock_id');
    }
    public function adjustedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'adjusted_by');
    }
}
