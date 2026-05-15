<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OrganizationUnit;
use App\Models\User;

class OrganizationUnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('organization-units.viewAny');
    }

    public function view(User $user, OrganizationUnit $unit): bool
    {
        return $user->can('organization-units.view');
    }

    public function create(User $user): bool
    {
        return $user->can('organization-units.create');
    }

    public function update(User $user, OrganizationUnit $unit): bool
    {
        return $user->can('organization-units.update');
    }

    public function archive(User $user, OrganizationUnit $unit): bool
    {
        return $user->can('organization-units.delete') || $user->can('organization-units.archive');
    }

    public function restore(User $user, OrganizationUnit $unit): bool
    {
        return $user->can('organization-units.restore');
    }

    public function viewDeleted(User $user): bool
    {
        return $user->can('organization-units.viewDeleted');
    }

    public function manageHierarchy(User $user): bool
    {
        return $user->can('organization-units.manageHierarchy');
    }
}
