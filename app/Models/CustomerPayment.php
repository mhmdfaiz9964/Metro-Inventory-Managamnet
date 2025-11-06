<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'paid_amount',
        'paid_date',
        'payment_method',
        'paid_bank_account_id',
    ];
    // Relationship to Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relationship to Sale (Invoice)
    public function invoice()
    {
        return $this->belongsTo(\App\Models\Sale::class, 'invoice_id');
    }

    // Optional: relationship to Bank
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'paid_bank_account_id');
    }
}
