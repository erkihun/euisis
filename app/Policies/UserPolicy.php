<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('users.viewAny');
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->can('users.view') || $actor->id === $target->id;
    }

    public function create(User $actor): bool
    {
        return $actor->can('users.create');
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->can('users.update');
    }

    public function delete(User $actor, User $model): bool
    {
        if ($actor->id === $model->id) {
            return false;
        }

        if ($this->isLastActiveSuperAdmin($model)) {
            return false;
        }

        return $actor->can('users.delete');
    }

    public function deactivate(User $actor, User $model): bool
    {
        if ($actor->id === $model->id) {
            return false;
        }

        if ($this->isLastActiveSuperAdmin($model)) {
            return false;
        }

        return $actor->can('users.deactivate');
    }

    public function archive(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return false;
        }

        if ($this->isLastActiveSuperAdmin($target)) {
            return false;
        }

        return $actor->can('users.archive');
    }

    public function restore(User $actor, User $target): bool
    {
        return $actor->can('users.restore');
    }

    public function assignRoles(User $actor, User $target): bool
    {
        return $actor->can('users.assignRoles');
    }

    public function resetPassword(User $actor, User $target): bool
    {
        return $actor->can('users.resetPassword');
    }

    private function isLastActiveSuperAdmin(User $model): bool
    {
        if (! $model->hasRole('Super Admin')) {
            return false;
        }

        return User::role('Super Admin')
            ->where('id', '!=', $model->id)
            ->where('status', 'active')
            ->count() === 0;
    }
}
