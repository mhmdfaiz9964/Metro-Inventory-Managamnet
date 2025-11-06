<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundTransfer extends Model
{
    use HasFactory;

    protected $table = 'fund_transfers';

    protected $fillable = [
        'reason',
        'bank_id',
        'to_bank_id',
        'type',
        'transfer_date',
        'transferred_by',
        'approved_by',
        'status',
        'note',
        'amount',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function fromBank()
    {
        return $this->belongsTo(BankAccount::class, 'bank_id', 'id');
    }

    public function toBank()
    {
        return $this->belongsTo(BankAccount::class, 'to_bank_id', 'id');
    }

    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'transferred_by', 'id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Boot method for auto transaction logging
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($fundTransfer) {
            // Only log when status is changed to completed
            if ($fundTransfer->isDirty('status') && $fundTransfer->status === 'completed') {
                $fundTransfer->logTransaction();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Log transaction when transfer is completed
    |--------------------------------------------------------------------------
    */
    public function logTransaction()
    {
        Transaction::create([
            'transaction_id' => uniqid('TXN-'),
            'cheque_id'      => null,
            'created_by'     => $this->transferred_by,
            'updated_by'     => $this->approved_by,
            'amount'         => $this->amount,
            'from_bank_id'   => $this->bank_id,
            'to_bank_id'     => $this->to_bank_id,
            'type'           => $this->type === 'bank_to_bank' ? 'credited' : 'debited',
        ]);

        // 🔹 Update bank balances too
        if ($this->fromBank) {
            $this->fromBank->decrement('bank_balance', $this->amount);
        }

        if ($this->type === 'bank_to_bank' && $this->toBank) {
            $this->toBank->increment('bank_balance', $this->amount);
        }
    }
}
