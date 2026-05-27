<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CafeteriaSubsidyLedger;
use App\Models\User;

readonly class CafeteriaSubsidyLedgerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cafeteria_ledger.viewAny');
    }

    public function view(User $user, CafeteriaSubsidyLedger $ledger): bool
    {
        return $user->can('cafeteria_ledger.view');
    }
}
