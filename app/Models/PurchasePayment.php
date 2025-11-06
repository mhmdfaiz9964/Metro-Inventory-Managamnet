<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'payment_method',
        'payment_amount',
        'discount',
        'discount_type',
        'payment_date',
        'bank_id',
        'bank_account_id',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'payment_amount' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
