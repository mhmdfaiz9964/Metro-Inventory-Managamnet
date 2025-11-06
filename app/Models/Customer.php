<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'mobile_number', 'mobile_number_2', 'email', 'note', 'balance_due', 'total_paid', 'credit_limit'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function saleReturns()
    {
        return $this->hasMany(SaleReturn::class);
    }
}
