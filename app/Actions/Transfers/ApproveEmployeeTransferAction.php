<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Enums\TransferStatus;
use App\Models\EmployeeTransfer;
use App\Models\User;

readonly class ApproveEmployeeTransferAction
{
    public function __construct(
        private WriteAuditLogAction $writeAuditLogAction,
        private CompleteEmployeeTransferAction $completeEmployeeTransferAction,
    ) {}

    public function execute(EmployeeTransfer $transfer, User $actor): EmployeeTransfer
    {
        $transfer->update([
            'status' => TransferStatus::Approved,
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        $this->writeAuditLogAction->execute(
            AuditEventType::TransferApproved,
            $actor,
            $transfer->fresh(),
            $transfer->to_organization_id,
            newValues: $transfer->fresh()->toArray(),
        );

        return $this->completeEmployeeTransferAction->execute($transfer->fresh(), $actor);
    }
}
