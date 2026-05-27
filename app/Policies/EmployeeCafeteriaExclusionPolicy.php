<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmployeeCafeteriaExclusion;
use App\Models\User;

readonly class EmployeeCafeteriaExclusionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cafeteria_employee_exclusions.viewAny');
    }

    public function view(User $user, EmployeeCafeteriaExclusion $exclusion): bool
    {
        return $user->can('cafeteria_employee_exclusions.view');
    }

    public function create(User $user): bool
    {
        return $user->can('cafeteria_employee_exclusions.create');
    }

    public function update(User $user, EmployeeCafeteriaExclusion $exclusion): bool
    {
        return $user->can('cafeteria_employee_exclusions.update');
    }

    public function end(User $user, EmployeeCafeteriaExclusion $exclusion): bool
    {
        return $user->can('cafeteria_employee_exclusions.end');
    }

    public function archive(User $user, EmployeeCafeteriaExclusion $exclusion): bool
    {
        return $user->can('cafeteria_employee_exclusions.delete')
            || $user->can('cafeteria_employee_exclusions.archive');
    }

    public function restore(User $user, EmployeeCafeteriaExclusion $exclusion): bool
    {
        return $user->can('cafeteria_employee_exclusions.restore');
    }
}
