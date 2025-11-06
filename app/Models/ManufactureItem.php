<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufactureItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufacture_id',
        'bom_component_id',
        'required_qty',
        'issued_qty',
        'consumed_qty',
    ];

    /**
     * Parent manufacture order
     */
    public function manufacture()
    {
        return $this->belongsTo(Manufacture::class, 'manufacture_id');
    }

    /**
     * BOM component used in this manufacture
     */
    public function bomComponent()
    {
        return $this->belongsTo(BomComponent::class, 'bom_component_id');
    }

    /**
     * Check if issued quantity meets required
     */
    public function isFullyIssued(): bool
    {
        return $this->issued_qty >= $this->required_qty;
    }

    /**
     * Check if consumed quantity matches issued
     */
    public function isFullyConsumed(): bool
    {
        return $this->consumed_qty >= $this->issued_qty;
    }
}
