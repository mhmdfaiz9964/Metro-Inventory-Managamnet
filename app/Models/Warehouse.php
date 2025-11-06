<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'type',
        'status',
        'created_by',
    ];

    /**
     * The user who created the warehouse.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Optional: stocks in this warehouse.
     */
    public function stocks()
    {
        return $this->hasMany(Stocks::class, 'location_id');
    }

    /**
     * Optional: raw material stocks in this warehouse.
     */
    public function bomStocks()
    {
        return $this->hasMany(BomStock::class, 'location_id');
    }
}
