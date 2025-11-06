<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseManufacturedProductPayment extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_manufactured_product_id', 'payment_method', 'bank_id', 'bank_account_id', 'amount', 'date'];

    public function purchase()
    {
        return $this->belongsTo(PurchaseManufacturedProduct::class, 'purchase_manufactured_product_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
}
