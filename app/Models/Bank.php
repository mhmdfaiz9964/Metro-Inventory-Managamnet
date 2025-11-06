<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $table = 'banks';

    protected $primaryKey = 'id';

    protected $fillable = ['id', 'name'];

    public function branches()
    {
        return $this->hasMany(Branch::class, 'bank_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'bank_id');
    }
}
