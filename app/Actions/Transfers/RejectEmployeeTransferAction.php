<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Enums\TransferStatus;
use App\Models\EmployeeTransfer;
use App\Models\User;

readonly class RejectEmployeeTransferAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(EmployeeTransfer $transfer, User $actor, string $reason): EmployeeTransfer
    {
        $transfer->update([
            'status' => TransferStatus::Rejected,
            'rejected_by' => $actor->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->writeAuditLogAction->execute(
            AuditEventType::TransferRejected,
            $actor,
            $transfer->fresh(),
            $transfer->from_organization_id,
            newValues: $transfer->fresh()->toArray(),
            reason: $reason,
        );

        return $transfer->fresh();
    }
}
