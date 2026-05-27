<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CafeteriaTransactionConsumedDay extends Model
{
    use HasUuidPrimaryKey;

    protected $fillable = [
        'cafeteria_transaction_id',
        'employee_id',
        'consumed_date',
        'subsidy_amount',
        'is_working_day',
        'source',
        'reversed_at',
        'reversed_by',
        'reversal_transaction_id',
    ];

    protected $casts = [
        'consumed_date' => 'date',
        'subsidy_amount' => 'decimal:2',
        'is_working_day' => 'boolean',
        'reversed_at' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CafeteriaTransaction::class, 'cafeteria_transaction_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
