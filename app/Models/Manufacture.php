<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manufacture extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'assigned_user_id',
        'start_date',
        'end_date',
        'status',
        'quantity_to_produce',
        'quantity_produced',
        'material_cost',
        'labor_cost',
        'overhead_cost',
        'total_cost',
        'unit_cost',
    ];

    /**
     * Product being manufactured
     */
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    /**
     * User assigned to supervise this manufacture
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Items required for this manufacture
     */
    public function manufactureItems()
    {
        return $this->hasMany(ManufactureItem::class, 'manufacture_id');
    }

    /**
     * Shortcut alias for items()
     */
    public function items()
    {
        return $this->hasMany(ManufactureItem::class, 'manufacture_id');
    }

    /**
     * Auto-calculate total cost (materials + labor + overhead)
     */
    public function calculateTotalCost(): float
    {
        return ($this->material_cost ?? 0)
             + ($this->labor_cost ?? 0)
             + ($this->overhead_cost ?? 0);
    }

    /**
     * Calculate cost per unit
     */
    public function calculateUnitCost(): ?float
    {
        if ($this->quantity_produced > 0) {
            return $this->calculateTotalCost() / $this->quantity_produced;
        }
        return null;
    }
}
