<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\TransferAnnouncementStatus;
use App\Models\TransferAnnouncement;
use App\Models\User;
use App\Policies\Concerns\DeniesNonAdminUsers;

class TransferAnnouncementPolicy
{
    use DeniesNonAdminUsers;

    public function viewAny(User $user): bool
    {
        return $user->can('transfers.announcements.view')
            || $user->can('transfers.announcements.viewAny');
    }

    public function view(User $user, TransferAnnouncement $announcement): bool
    {
        return $user->can('transfers.announcements.view');
    }

    public function create(User $user): bool
    {
        return $user->can('transfers.announcements.create');
    }

    /** Only Draft announcements may be edited/updated. */
    public function update(User $user, TransferAnnouncement $announcement): bool
    {
        return $user->can('transfers.announcements.update')
            && $announcement->status === TransferAnnouncementStatus::Draft;
    }

    public function publish(User $user, TransferAnnouncement $announcement): bool
    {
        return $user->can('transfers.announcements.publish');
    }

    /**
     * Permission gate for close route.
     * Status validation is handled inside CloseTransferAnnouncementAction (throws DomainException).
     * The controller catches that and redirects with an error flash — not a 403.
     */
    public function close(User $user, TransferAnnouncement $announcement): bool
    {
        return $user->can('transfers.announcements.close');
    }

    /**
     * Cancel shares the 'close' permission in the test suite.
     * Status finality is enforced inside CancelTransferAnnouncementAction.
     */
    public function cancel(User $user, TransferAnnouncement $announcement): bool
    {
        return $user->can('transfers.announcements.cancel')
            || $user->can('transfers.announcements.close');
    }

    /** Deletion uses the 'update' permission (drafts only). */
    public function delete(User $user, TransferAnnouncement $announcement): bool
    {
        return $user->can('transfers.announcements.update')
            || $user->can('transfers.announcements.delete');
    }

    public function restore(User $user, TransferAnnouncement $announcement): bool
    {
        return $user->can('transfers.announcements.restore');
    }
}
