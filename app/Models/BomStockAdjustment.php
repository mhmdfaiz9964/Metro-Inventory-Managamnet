<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomStockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bom_stock_id',
        'adjustment_type',
        'reason_type',
        'quantity',
        'adjusted_by',
    ];

    public function bomStock()
    {
        return $this->belongsTo(BomStock::class, 'bom_stock_id');
    }

    public function adjustedByUser()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
