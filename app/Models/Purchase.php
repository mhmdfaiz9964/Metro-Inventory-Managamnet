<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'product_id',
        'purchase_date',
        'paid_amount',
        'grand_total',
        'notes',
        'payment_method', // cash / cheque
        'payment_status', // paid / not paid
        'bank_account_id', // optional, required if cheque
        'cheque_no', // nullable
        'cheque_date', // nullable
        'paid_to', // nullable
        'created_by',
        'status', // pending / received
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'cheque_date' => 'datetime',
    ];

    /**
     * Relationships
     */

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function products()
    {
        return $this->hasMany(PurchaseProduct::class);
    }
    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
    public function bomProducts()
    {
        return $this->hasMany(PurchaseBomProduct::class, 'purchase_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseBomProduct::class, 'purchase_id');
    }
    public function loans()
    {
        return $this->hasMany(Loan::class, 'purchase_id');
    }
}
