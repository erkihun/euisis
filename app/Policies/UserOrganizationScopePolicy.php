<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserOrganizationScope;

class UserOrganizationScopePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('users.assignOrganizationScopes') || $actor->can('user-organization-scopes.viewAny');
    }

    public function create(User $actor): bool
    {
        return $actor->can('user-organization-scopes.create');
    }

    public function update(User $actor, UserOrganizationScope $scope): bool
    {
        return $actor->can('user-organization-scopes.update');
    }

    public function delete(User $actor, UserOrganizationScope $scope): bool
    {
        return $actor->can('user-organization-scopes.delete');
    }

    public function restore(User $actor, UserOrganizationScope $scope): bool
    {
        return $actor->can('user-organization-scopes.restore');
    }
}
