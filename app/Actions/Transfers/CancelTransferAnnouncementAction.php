<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Enums\TransferAnnouncementStatus;
use App\Models\TransferAnnouncement;
use App\Models\User;
use DomainException;

readonly class CancelTransferAnnouncementAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(TransferAnnouncement $announcement, User $actor): TransferAnnouncement
    {
        if ($announcement->status->isFinal()) {
            throw new DomainException(__('transfers.cancelNotAllowed'));
        }

        $old = $announcement->status->value;

        $announcement->update(['status' => TransferAnnouncementStatus::Cancelled->value]);

        $this->writeAuditLogAction->execute(
            AuditEventType::TransferAnnouncementCancelled,
            $actor,
            $announcement->fresh(),
            $announcement->organization_id,
            oldValues: ['status' => $old],
            newValues: ['status' => TransferAnnouncementStatus::Cancelled->value],
        );

        return $announcement->fresh();
    }
}
