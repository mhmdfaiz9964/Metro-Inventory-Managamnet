<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;

class Cheques extends Model
{
    use HasFactory;

    protected $table = 'cheques';

    protected $fillable = ['reason', 'type', 'note', 'cheque_date', 'cheque_bank', 'amount', 'created_by', 'approved_by', 'status', 'cheque_no', 'paid_to'];
    protected $casts = [
        'cheque_date' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function bank()
    {
        return $this->belongsTo(BankAccount::class, 'cheque_bank', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Boot method for auto transaction logging
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($cheque) {
            // Check if status was changed to "approved"
            if ($cheque->isDirty('status') && $cheque->status === 'approved') {
                $cheque->logTransaction();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Log transaction when cheque is completed
    |--------------------------------------------------------------------------
    */
    public function logTransaction()
    {
        Transaction::create([
            'transaction_id' => uniqid('TXN-'),
            'cheque_id' => $this->id,
            'created_by' => $this->created_by,
            'updated_by' => $this->approved_by,
            'amount' => $this->amount,
            'from_bank_id' => null, // money comes from cheque bank
            'to_bank_id' => $this->cheque_bank,
            'type' => $this->type === 'credit' ? 'credited' : 'debited',
        ]);
    }
}
