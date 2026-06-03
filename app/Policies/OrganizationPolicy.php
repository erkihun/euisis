<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\User;
use App\Services\OrganizationScope\OrganizationScopeService;
use App\Policies\Concerns\DeniesNonAdminUsers;

readonly class OrganizationPolicy
{
    use DeniesNonAdminUsers;

    public function __construct(private OrganizationScopeService $organizationScopeService) {}

    public function viewAny(User $user): bool
    {
        return $user->can('organizations.view');
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->can('organizations.view')
            && $this->organizationScopeService->canAccessOrganization($user, $organization->id);
    }

    public function create(User $user): bool
    {
        return $user->can('organizations.manage');
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->can('organizations.manage');
    }

    public function archive(User $user, Organization $organization): bool
    {
        return $user->can('organizations.manage');
    }

    public function createChild(User $user, Organization $organization): bool
    {
        if (! $user->can('organizations.manage')) {
            return false;
        }

        if ($organization->status !== OrganizationStatus::Active) {
            return false;
        }

        return $this->organizationScopeService->canAccessOrganization($user, $organization->id);
    }

    public function manageHierarchy(User $user): bool
    {
        return $user->can('organizations.manage');
    }
}
