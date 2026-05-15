<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

readonly class CreateUserAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(array $attributes, User $actor): User
    {
        return DB::transaction(function () use ($attributes, $actor): User {
            $roles = $attributes['roles'] ?? [];
            unset($attributes['roles']);

            $attributes['password'] = Hash::make($attributes['password']);
            $attributes['status'] = $attributes['status'] ?? 'active';

            $user = User::query()->create($attributes);

            if (! empty($roles)) {
                $user->syncRoles($roles);
            }

            $this->writeAuditLogAction->execute(
                AuditEventType::UserCreated,
                $actor,
                $user,
                null,
                newValues: [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'gender' => $user->gender,
                    'roles' => $roles,
                ],
            );

            return $user;
        });
    }
}
