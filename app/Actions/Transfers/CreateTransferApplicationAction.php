<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Enums\EmployeeStatus;
use App\Enums\TransferApplicationStatus;
use App\Models\Employee;
use App\Models\TransferAnnouncement;
use App\Models\TransferApplication;
use App\Models\TransferSetting;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

readonly class CreateTransferApplicationAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(
        TransferAnnouncement $announcement,
        Employee $employee,
        User $actor,
        array $data = [],
    ): TransferApplication {
        $this->ensureCanApply($announcement, $employee);

        $settings = TransferSetting::current();
        $assignment = $employee->currentAssignment;

        if ($assignment === null) {
            throw new DomainException('Employee has no current assignment.');
        }

        $eligibilitySnapshot = $this->buildEligibilitySnapshot($employee, $announcement, $settings);

        return DB::transaction(function () use ($announcement, $employee, $actor, $data, $assignment, $eligibilitySnapshot): TransferApplication {
            $application = TransferApplication::query()->create([
                'announcement_id' => $announcement->id,
                'employee_id' => $employee->id,
                'current_assignment_id' => $assignment->id,
                'releasing_organization_id' => $assignment->organization_id,
                'receiving_organization_id' => $announcement->organization_id,
                'status' => TransferApplicationStatus::Submitted->value,
                'eligibility_snapshot' => $eligibilitySnapshot,
                'applicant_notes' => $data['applicant_notes'] ?? null,
                'submitted_at' => now(),
            ]);

            $this->writeAuditLogAction->execute(
                AuditEventType::TransferApplicationSubmitted,
                $actor,
                $application,
                $assignment->organization_id,
                newValues: $application->toArray(),
            );

            return $application;
        });
    }

    private function ensureCanApply(TransferAnnouncement $announcement, Employee $employee): void
    {
        if ($employee->status !== EmployeeStatus::Active) {
            throw new DomainException('Only active employees can apply for transfers.');
        }

        if ($employee->currentAssignment === null) {
            throw new DomainException('Employee must have a current active assignment.');
        }

        if (! $announcement->isAcceptingApplications()) {
            throw new DomainException('This announcement is not currently accepting applications.');
        }

        $alreadyApplied = TransferApplication::query()
            ->where('announcement_id', $announcement->id)
            ->where('employee_id', $employee->id)
            ->whereNotIn('status', [
                TransferApplicationStatus::Withdrawn->value,
                TransferApplicationStatus::Cancelled->value,
            ])
            ->exists();

        if ($alreadyApplied) {
            throw new DomainException('You have already applied for this transfer announcement.');
        }

        // Prevent multiple pending transfers if settings disallow it
        $settings = TransferSetting::current();
        if (! $settings->allow_cross_institution) {
            $currentOrgId = $employee->currentAssignment->organization_id;
            if ($currentOrgId === $announcement->organization_id) {
                throw new DomainException('Transfer to the same organization is not permitted.');
            }
        }
    }

    private function buildEligibilitySnapshot(Employee $employee, TransferAnnouncement $announcement, TransferSetting $settings): array
    {
        $assignment = $employee->currentAssignment;

        return [
            'captured_at' => now()->toISOString(),
            'employee_status' => $employee->status->value,
            'current_org_id' => $assignment?->organization_id,
            'current_position_id' => $assignment?->position_id,
            'require_same_position' => $settings->require_same_position,
            'require_same_grade' => $settings->require_same_grade,
            'settings_snapshot' => [
                'minimum_service_months' => $settings->minimum_service_months,
            ],
        ];
    }
}
