<?php

declare(strict_types=1);

namespace App\Actions\Cafeteria;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Enums\CafeteriaTransactionStatus;
use App\Models\CafeteriaTransaction;
use App\Models\User;
use App\Services\Cafeteria\CafeteriaLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

readonly class ReverseCafeteriaTransactionAction
{
    public function __construct(
        private CafeteriaLedgerService $ledgerService,
        private WriteAuditLogAction $writeAuditLogAction,
    ) {}

    public function execute(CafeteriaTransaction $transaction, User $actor, ?string $reason = null, ?Request $request = null): CafeteriaTransaction
    {
        if ($transaction->isReversed()) {
            throw ValidationException::withMessages(['transaction' => [__('cafeteria.alreadyReversed')]]);
        }

        if (! $transaction->isAccepted()) {
            throw ValidationException::withMessages(['transaction' => [__('cafeteria.cannotReverseStatus')]]);
        }

        DB::transaction(function () use ($transaction, $actor): void {
            $transaction->forceFill(['status' => CafeteriaTransactionStatus::Reversed])->save();

            $transaction->consumedDays()
                ->whereNull('reversed_at')
                ->update([
                    'reversed_at' => now(),
                    'reversed_by' => $actor->id,
                    'reversal_transaction_id' => $transaction->id,
                ]);

            // Write a reversal ledger entry to restore the ledger balance
            $reversalAmount = $transaction->is_extra_scan
                ? $transaction->deduction_amount   // refund the carry-forward deduction
                : $transaction->subsidy_amount_applied; // refund the subsidy net usage

            if ((float) $reversalAmount > 0) {
                $this->ledgerService->recordReversal(
                    $transaction->employee,
                    (float) $reversalAmount,
                    Carbon::parse($transaction->transaction_date),
                    $transaction,
                    $actor,
                );
            }
        });

        $this->writeAuditLogAction->execute(
            AuditEventType::CafeteriaTransactionReversed,
            $actor,
            $transaction,
            $transaction->provider?->organization_id,
            newValues: [
                'transaction_number' => $transaction->transaction_number,
                'employee_id' => $transaction->employee_id,
            ],
            reason: $reason,
            request: $request,
        );

        return $transaction;
    }
}
