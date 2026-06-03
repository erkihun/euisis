<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Enums\TransferAnnouncementStatus;
use App\Models\TransferAnnouncement;
use App\Models\User;
use DomainException;

readonly class CloseTransferAnnouncementAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(TransferAnnouncement $announcement, User $actor): TransferAnnouncement
    {
        if ($announcement->status !== TransferAnnouncementStatus::Published) {
            throw new DomainException(__('transfers.announcementNotPublished'));
        }

        $announcement->update(['status' => TransferAnnouncementStatus::Closed->value]);

        $this->writeAuditLogAction->execute(
            AuditEventType::TransferAnnouncementClosed,
            $actor,
            $announcement->fresh(),
            $announcement->organization_id,
            oldValues: ['status' => TransferAnnouncementStatus::Published->value],
            newValues: ['status' => TransferAnnouncementStatus::Closed->value],
        );

        return $announcement->fresh();
    }
}
