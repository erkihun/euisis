<?php

declare(strict_types=1);

namespace App\Actions\Cafeteria;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Models\CafeteriaProvider;
use App\Models\ServiceProvider;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

readonly class CreateCafeteriaProviderAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(array $attributes, User $actor, ?Request $request = null): CafeteriaProvider
    {
        $provider = CafeteriaProvider::query()->create([
            'code'                => strtoupper(trim($attributes['code'])),
            'name_en'             => trim($attributes['name_en']),
            'name_am'             => isset($attributes['name_am']) ? trim($attributes['name_am']) : null,
            'organization_id'     => $attributes['organization_id'] ?? null,
            'assigned_scope_type' => $attributes['assigned_scope_type'] ?? 'self',
            'contact_person'      => $attributes['contact_person'] ?? null,
            'phone_number'        => $attributes['phone_number'] ?? null,
            'email'               => $attributes['email'] ?? null,
            'location'            => $attributes['location'] ?? null,
            'is_active'           => (bool) ($attributes['is_active'] ?? true),
            'created_by'          => $actor->id,
            'updated_by'          => $actor->id,
        ]);

        // Establish shared identity in the ServiceProvider registry
        $this->linkToServiceProviderRegistry($provider);

        $this->writeAuditLogAction->execute(
            AuditEventType::CafeteriaProviderCreated,
            $actor,
            $provider,
            $provider->organization_id,
            newValues: [
                'code'                => $provider->code,
                'name_en'             => $provider->name_en,
                'organization_id'     => $provider->organization_id,
                'assigned_scope_type' => $provider->assigned_scope_type,
            ],
            request: $request,
        );

        if ($provider->organization_id !== null) {
            $this->writeAuditLogAction->execute(
                AuditEventType::CafeteriaProviderInstitutionAssigned,
                $actor,
                $provider,
                $provider->organization_id,
                newValues: [
                    'organization_id'     => $provider->organization_id,
                    'assigned_scope_type' => $provider->assigned_scope_type,
                ],
                request: $request,
            );
        }

        return $provider;
    }

    private function linkToServiceProviderRegistry(CafeteriaProvider $provider): void
    {
        $cafeteriaType = ServiceType::where('code', 'cafeteria')->first();

        if (! $cafeteriaType) {
            return;
        }

        $code = $provider->code;
        if (ServiceProvider::where('code', $code)->exists()) {
            $code = 'CAF-' . $code . '-' . Str::upper(Str::random(4));
        }

        $serviceProvider = ServiceProvider::query()->create([
            'service_type_id' => $cafeteriaType->id,
            'organization_id' => $provider->organization_id,
            'name'            => $provider->name_en,
            'code'            => $code,
            'status'          => $provider->is_active ? 'active' : 'inactive',
            'is_demo'         => false,
        ]);

        $provider->withoutTimestamps(fn () => $provider->updateQuietly([
            'service_provider_id' => $serviceProvider->id,
        ]));
    }
}
