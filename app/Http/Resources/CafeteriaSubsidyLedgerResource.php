<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CafeteriaSubsidyLedgerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'employee_id'              => $this->employee_id,
            'cafeteria_transaction_id' => $this->cafeteria_transaction_id,
            'ledger_date'              => $this->ledger_date?->toDateString(),
            'entry_type'               => $this->entry_type?->value,
            'amount'                   => (float) $this->amount,
            'balance_after'            => (float) $this->balance_after,
            'working_day'              => $this->working_day,
            'description'              => $this->description,
            'created_at'               => $this->created_at?->toISOString(),
        ];
    }
}
