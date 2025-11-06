<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnCheque extends Model
{
    use HasFactory;

    protected $table = 'return_cheques';

    protected $fillable = ['cheque_no', 'return_cheque_no', 'return_date', 'amount', 'cheque_bank', 'return_reason','type'];
    protected $casts = [
        'return_date' => 'datetime:Y-m-d',
    ];
    /**
     * Boot method to auto-generate return_cheque_no
     */
    public function bank()
    {
        return $this->belongsTo(BankAccount::class, 'cheque_bank');
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->return_cheque_no = 'RCN-' . strtoupper(uniqid());
        });
    }
}
