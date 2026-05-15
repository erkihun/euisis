<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Actions\Audit\WriteAuditLogAction;
use App\Actions\Entitlements\RecalculateEntitlementsAction;
use App\Enums\AssignmentStatus;
use App\Enums\AuditEventType;
use App\Enums\TransferStatus;
use App\Models\EmployeeAssignment;
use App\Models\EmployeeTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

readonly class CompleteEmployeeTransferAction
{
    public function __construct(
        private RecalculateEntitlementsAction $recalculateEntitlementsAction,
        private WriteAuditLogAction $writeAuditLogAction,
    ) {}

    public function execute(EmployeeTransfer $transfer, User $actor): EmployeeTransfer
    {
        return DB::transaction(function () use ($transfer, $actor): EmployeeTransfer {
            $employee = $transfer->employee()->lockForUpdate()->firstOrFail();
            $currentAssignment = $employee->currentAssignment()->lockForUpdate()->firstOrFail();

            $currentAssignment->update([
                'assignment_status' => AssignmentStatus::Closed,
                'is_current' => false,
                'effective_to' => $transfer->effective_date->toDateString(),
                'reason' => $transfer->transfer_reason,
            ]);

            $newAssignment = EmployeeAssignment::query()->create([
                'employee_id' => $employee->id,
                'organization_id' => $transfer->to_organization_id,
                'position_id' => $transfer->to_position_id,
                'hierarchy_version_id' => $currentAssignment->hierarchy_version_id,
                'assignment_status' => AssignmentStatus::Active,
                'effective_from' => $transfer->effective_date->toDateString(),
                'effective_to' => null,
                'is_current' => true,
                'reason' => $transfer->transfer_reason,
            ]);

            $employee->update(['current_assignment_id' => $newAssignment->id]);

            foreach ($employee->entitlements as $entitlement) {
                $this->recalculateEntitlementsAction->execute($entitlement);
            }

            $transfer->update([
                'status' => TransferStatus::Completed,
                'completed_at' => now(),
            ]);

            $this->writeAuditLogAction->execute(
                AuditEventType::AssignmentChanged,
                $actor,
                $employee->fresh(),
                $transfer->to_organization_id,
                oldValues: $currentAssignment->toArray(),
                newValues: $newAssignment->toArray(),
                reason: $transfer->transfer_reason,
            );

            $this->writeAuditLogAction->execute(
                AuditEventType::TransferCompleted,
                $actor,
                $transfer->fresh(),
                $transfer->to_organization_id,
                newValues: $transfer->fresh()->toArray(),
            );

            return $transfer->fresh(['employee.currentAssignment', 'fromOrganization', 'toOrganization', 'fromPosition', 'toPosition']);
        });
    }
}
