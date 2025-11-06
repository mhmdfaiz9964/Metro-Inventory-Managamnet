<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'customer_id',
        'return_date',
        'total_amount',
        'reason',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
