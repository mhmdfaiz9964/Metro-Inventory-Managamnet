<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    use HasFactory;

    protected $table = 'sales_payments';

    protected $fillable = ['sale_id', 'payment_method', 'payment_amount', 'discount', 'discount_type', 'payment_paid', 'paid_by', 'paid_date', 'bank_account_id'];

    /**
     * A payment belongs to a sale.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_account_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'paid_by');
    }
}
