<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = 'bank_accounts';

    protected $fillable = ['bank_name', 'branch_name', 'owner_name', 'account_number', 'bank_balance', 'status'];

    public function transactionsFrom()
    {
        return $this->hasMany(Transaction::class, 'from_bank_id');
    }

    public function transactionsTo()
    {
        return $this->hasMany(Transaction::class, 'to_bank_id');
    }

    public function receivingCheques()
    {
        return $this->hasMany(ReceivingCheque::class, 'bank_id', 'id');
    }

    public function fundTransfersFrom()
    {
        return $this->hasMany(FundTransfer::class, 'bank_id', 'id');
    }

    public function fundTransfersTo()
    {
        return $this->hasMany(FundTransfer::class, 'to_bank_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(ReceivingPayment::class, 'bank_id', 'id');
    }
    public function cheques()
    {
        return $this->hasMany(Cheques::class, 'cheque_bank', 'id');
    }
}
