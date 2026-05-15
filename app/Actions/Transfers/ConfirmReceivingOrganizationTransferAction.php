<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Enums\TransferStatus;
use App\Models\EmployeeTransfer;
use App\Models\User;

readonly class ConfirmReceivingOrganizationTransferAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(EmployeeTransfer $transfer, User $actor): EmployeeTransfer
    {
        $transfer->update([
            'receiving_organization_confirmed_by' => $actor->id,
            'receiving_org_confirmed_at' => now(),
            'status' => TransferStatus::ReceivingOrganizationConfirmed,
        ]);

        $this->writeAuditLogAction->execute(
            AuditEventType::TransferReceivingOrganizationConfirmed,
            $actor,
            $transfer->fresh(),
            $transfer->to_organization_id,
            newValues: $transfer->fresh()->toArray(),
        );

        return $transfer->fresh();
    }
}
