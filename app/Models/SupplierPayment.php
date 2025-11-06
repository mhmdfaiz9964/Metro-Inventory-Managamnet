<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id', 'purchase_id', 'payment_method', 'payment_amount', 'payment_date', 'bank_id', 'notes'];
    protected $casts = [
        'payment_date' => 'datetime',
    ];
    /**
     * Get the supplier associated with this payment.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
    /**
     * Get the bank associated with this payment (if any).
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
}
