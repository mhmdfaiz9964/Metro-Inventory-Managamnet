<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivingPayment extends Model
{
    protected $fillable = ['reason', 'paid_date', 'paid_by', 'status', 'bank_id', 'amount'];

    protected $casts = [
        'paid_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function bank()
    {
        return $this->belongsTo(BankAccount::class, 'bank_id');
    }
}
