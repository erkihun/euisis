<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Enums\TransferAnnouncementStatus;
use App\Models\PositionEstablishment;
use App\Models\TransferAnnouncement;
use App\Models\User;
use DomainException;

readonly class PublishTransferAnnouncementAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(TransferAnnouncement $announcement, User $actor): TransferAnnouncement
    {
        if ($announcement->status !== TransferAnnouncementStatus::Draft) {
            throw new DomainException(__('transfers.announcementNotDraft'));
        }

        if ($announcement->organization_id === null) {
            throw new DomainException(__('transfers.publishMissingOrganization'));
        }

        if ($announcement->position_id === null) {
            throw new DomainException(__('transfers.publishMissingPosition'));
        }

        if (($announcement->number_of_vacancies ?? 0) < 1) {
            throw new DomainException(__('transfers.publishNoVacancies'));
        }

        if ($announcement->opening_date === null || $announcement->closing_date === null) {
            throw new DomainException(__('transfers.publishMissingDates'));
        }

        if ($announcement->closing_date->lessThanOrEqualTo($announcement->opening_date)) {
            throw new DomainException(__('transfers.publishInvalidDateRange'));
        }

        $establishment = PositionEstablishment::query()
            ->where('organization_id', $announcement->organization_id)
            ->where('position_id', $announcement->position_id)
            ->where('status', 'approved')
            ->first();

        if ($establishment === null) {
            throw new DomainException(__('transfers.publishNoEstablishment'));
        }

        $announcement->update([
            'status' => TransferAnnouncementStatus::Published->value,
            'published_by' => $actor->id,
            'published_at' => now(),
        ]);

        $this->writeAuditLogAction->execute(
            AuditEventType::TransferAnnouncementPublished,
            $actor,
            $announcement->fresh(),
            $announcement->organization_id,
            oldValues: ['status' => TransferAnnouncementStatus::Draft->value],
            newValues: ['status' => TransferAnnouncementStatus::Published->value],
        );

        return $announcement->fresh();
    }
}
