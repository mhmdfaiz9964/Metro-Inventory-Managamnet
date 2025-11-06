<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    // Mass assignable fields
    protected $fillable = [
        'reference_number',
        'customer_id',
        'invoice_id',
        'purchase_id',
        'supplier_id',
        'type',
        'amount',
        'note',
        'loan_date',
        'due_date',
        'status',
    ];

    // Casts for dates
    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(\App\Models\Sale::class, 'invoice_id');
    }
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Enum helpers (optional but useful)
    public const TYPE_GIVEN = 'given';
    public const TYPE_RECEIVED = 'received';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_PARTIALLY_PAID = 'partially_paid';

    public static function types(): array
    {
        return [self::TYPE_GIVEN, self::TYPE_RECEIVED];
    }

    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_PAID, self::STATUS_PARTIALLY_PAID];
    }
}
