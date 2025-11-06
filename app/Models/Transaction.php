<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'cheque_id',
        'created_by',
        'updated_by',
        'amount',
        'from_bank_id',
        'to_bank_id',
        'type',
    ];
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
    public function fromBank()
    {
        return $this->belongsTo(BankAccount::class, 'from_bank_id');
    }

    public function toBank()
    {
        return $this->belongsTo(BankAccount::class, 'to_bank_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
