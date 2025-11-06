<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivingCheque extends Model
{
    use HasFactory;

    protected $table = 'receiving_cheques';

    protected $fillable = ['cheque_no', 'bank_id', 'paid_by', 'status', 'paid_date', 'cheque_date', 'amount', 'reason', 'cheque_type', 'paid_bank_account_id'];
    protected $casts = [
        'paid_date' => 'datetime',
        'cheque_date' => 'datetime',
    ];
    /**
     * Bank account relationship (if bank stored in BankAccount table)
     */
    public function paidBankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'paid_bank_account_id');
    }
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
    /**
     * Transactions associated with this cheque
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cheque_id');
    }
}
