<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'bom_component_id',
        'available_stock',
    ];

    public function bomComponent()
    {
        return $this->belongsTo(BomComponent::class, 'bom_component_id');
    }
}
