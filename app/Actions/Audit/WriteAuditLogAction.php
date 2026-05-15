<?php

declare(strict_types=1);

namespace App\Actions\Audit;

use App\Enums\AuditEventType;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WriteAuditLogAction
{
    public function execute(
        AuditEventType $eventType,
        ?User $actor,
        ?Model $auditable = null,
        ?string $organizationId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $reason = null,
        ?Request $request = null,
    ): AuditLog {
        $organizationId = $this->validOrganizationId($organizationId);

        return AuditLog::query()->create([
            'actor_user_id' => $actor?->getKey(),
            'actor_type' => $actor ? $actor::class : null,
            'event_type' => $eventType,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'organization_id' => $organizationId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'reason' => $reason,
            'request_id' => (string) Str::uuid7(),
            'request_ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }

    private function validOrganizationId(?string $organizationId): ?string
    {
        if ($organizationId === null) {
            return null;
        }

        return Organization::query()->whereKey($organizationId)->exists()
            ? $organizationId
            : null;
    }
}
