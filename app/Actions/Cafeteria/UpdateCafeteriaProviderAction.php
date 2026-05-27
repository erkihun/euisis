<?php

declare(strict_types=1);

namespace App\Actions\Cafeteria;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Models\CafeteriaProvider;
use App\Models\User;
use Illuminate\Http\Request;

readonly class UpdateCafeteriaProviderAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(CafeteriaProvider $provider, array $attributes, User $actor, ?Request $request = null): CafeteriaProvider
    {
        $oldOrganizationId = $provider->organization_id;
        $oldScopeType      = $provider->assigned_scope_type ?? 'self';
        $oldValues         = [
            'name_en'             => $provider->name_en,
            'is_active'           => $provider->is_active,
            'organization_id'     => $oldOrganizationId,
            'assigned_scope_type' => $oldScopeType,
        ];

        $provider->forceFill([
            'name_en'             => trim($attributes['name_en']),
            'name_am'             => isset($attributes['name_am']) ? trim($attributes['name_am']) : $provider->name_am,
            'organization_id'     => $attributes['organization_id'] ?? $provider->organization_id,
            'assigned_scope_type' => $attributes['assigned_scope_type'] ?? $provider->assigned_scope_type ?? 'self',
            'contact_person'      => $attributes['contact_person'] ?? $provider->contact_person,
            'phone_number'        => $attributes['phone_number'] ?? $provider->phone_number,
            'email'               => $attributes['email'] ?? $provider->email,
            'location'            => $attributes['location'] ?? $provider->location,
            'is_active'           => isset($attributes['is_active']) ? (bool) $attributes['is_active'] : $provider->is_active,
            'updated_by'          => $actor->id,
        ])->save();

        // Keep the ServiceProvider registry in sync
        $provider->serviceProvider?->update([
            'name'            => $provider->name_en,
            'organization_id' => $provider->organization_id,
            'status'          => $provider->is_active ? 'active' : 'inactive',
        ]);

        $this->writeAuditLogAction->execute(
            AuditEventType::CafeteriaProviderUpdated,
            $actor,
            $provider,
            $provider->organization_id,
            oldValues: $oldValues,
            newValues: [
                'name_en'             => $provider->name_en,
                'is_active'           => $provider->is_active,
                'organization_id'     => $provider->organization_id,
                'assigned_scope_type' => $provider->assigned_scope_type,
            ],
            request: $request,
        );

        // Log institution change separately when org or scope changed
        $institutionChanged = $provider->organization_id !== $oldOrganizationId
            || ($provider->assigned_scope_type ?? 'self') !== $oldScopeType;

        if ($institutionChanged) {
            $this->writeAuditLogAction->execute(
                AuditEventType::CafeteriaProviderInstitutionChanged,
                $actor,
                $provider,
                $provider->organization_id,
                oldValues: ['organization_id' => $oldOrganizationId, 'assigned_scope_type' => $oldScopeType],
                newValues: ['organization_id' => $provider->organization_id, 'assigned_scope_type' => $provider->assigned_scope_type],
                request: $request,
            );
        }

        return $provider;
    }
}
