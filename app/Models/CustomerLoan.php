<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'customer_name',
        'date',
        'status',
        'loan_due_date',
        'paid_date',
        'reason',
        'amount',
        'from_bank_account_id',
    ];

    // Optional relationship if you have a BankAccount model
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'from_bank_account_id');
    }
}
