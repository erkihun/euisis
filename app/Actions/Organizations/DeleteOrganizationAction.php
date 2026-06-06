<?php

declare(strict_types=1);

namespace App\Actions\Organizations;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

readonly class DeleteOrganizationAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(Organization $organization, User $actor): void
    {
        DB::transaction(function () use ($organization, $actor): void {
            $organization->forceFill([
                'deleted_by' => $actor->id,
            ])->save();

            $this->writeAuditLogAction->execute(
                AuditEventType::OrganizationUpdated,
                $actor,
                $organization,
                $organization->id,
                oldValues: ['deleted_at' => null],
                newValues: ['deleted_at' => now()->toDateTimeString()],
            );

            $organization->delete();
        });
    }
}
