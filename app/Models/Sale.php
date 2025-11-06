<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['salesperson_id', 'sale_date', 'total_amount', 'customer_id'];

    protected $casts = [
        'sale_date' => 'date',
    ];

    // Relationships
    public function salesperson()
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function salePayments()
    {
        return $this->hasMany(SalePayment::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class, 'invoice_id');
    }

    // Accessor for combined payments
    public function getPaymentsAttribute()
    {
        return $this->salePayments->merge($this->customerPayments);
    }

    // Optional helper method to get payments as a collection
    public function payments()
    {
        return $this->hasMany(SalePayment::class, 'sale_id');
    }

    // Helper methods
    public function totalAmount()
    {
        return $this->items->sum('total');
    }

    public function amountPaid()
    {
        return $this->salePayments->sum('paid_amount') + $this->customerPayments->sum('paid_amount');
    }

    public function balanceDue()
    {
        return $this->total_amount - $this->amountPaid();
    }

    public function isPaidInFull()
    {
        return $this->balanceDue() <= 0;
    }
}
