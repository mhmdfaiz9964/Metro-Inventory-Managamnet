<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'note',
    ];

    /**
     * A brand can have many BOM components.
     */
    public function bomComponents()
    {
        return $this->hasMany(BomComponent::class, 'brand_id');
    }
}
