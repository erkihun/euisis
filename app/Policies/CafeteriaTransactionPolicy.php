<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CafeteriaTransaction;
use App\Models\User;
use App\Services\Cafeteria\CafeteriaProviderAccessService;

readonly class CafeteriaTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cafeteria_transactions.viewAny');
    }

    public function view(User $user, CafeteriaTransaction $transaction): bool
    {
        return $user->can('cafeteria_transactions.view')
            && app(CafeteriaProviderAccessService::class)->canAccessProvider($user, $transaction->cafeteria_provider_id);
    }

    public function scan(User $user): bool
    {
        return $user->can('cafeteria_transactions.scan') || $user->hasRole(['Cafeteria Operator', 'Super Admin', 'City Admin']);
    }

    public function reverse(User $user, CafeteriaTransaction $transaction): bool
    {
        return $user->can('cafeteria_transactions.reverse')
            && app(CafeteriaProviderAccessService::class)->canAccessProvider($user, $transaction->cafeteria_provider_id);
    }
}
