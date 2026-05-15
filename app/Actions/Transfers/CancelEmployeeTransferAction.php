<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Enums\TransferStatus;
use App\Models\EmployeeTransfer;
use App\Models\User;

readonly class CancelEmployeeTransferAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(EmployeeTransfer $transfer, User $actor): EmployeeTransfer
    {
        $transfer->update([
            'status' => TransferStatus::Cancelled,
        ]);

        $this->writeAuditLogAction->execute(
            AuditEventType::TransferCancelled,
            $actor,
            $transfer->fresh(),
            $transfer->from_organization_id,
            newValues: $transfer->fresh()->toArray(),
        );

        return $transfer->fresh();
    }
}
