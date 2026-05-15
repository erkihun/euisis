<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Enums\TransferStatus;
use App\Models\EmployeeTransfer;
use App\Models\User;

readonly class ConfirmCurrentOrganizationTransferAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(EmployeeTransfer $transfer, User $actor): EmployeeTransfer
    {
        $status = $transfer->receiving_org_confirmed_at !== null
            ? TransferStatus::ReceivingOrganizationConfirmed
            : TransferStatus::CurrentOrganizationConfirmed;

        $transfer->update([
            'current_org_confirmed_by' => $actor->id,
            'current_org_confirmed_at' => now(),
            'status' => $status,
        ]);

        $this->writeAuditLogAction->execute(
            AuditEventType::TransferCurrentOrganizationConfirmed,
            $actor,
            $transfer->fresh(),
            $transfer->from_organization_id,
            newValues: $transfer->fresh()->toArray(),
        );

        return $transfer->fresh();
    }
}
