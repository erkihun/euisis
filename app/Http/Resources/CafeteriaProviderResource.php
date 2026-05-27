<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CafeteriaProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        $org = $this->whenLoaded('organization', fn () => $this->organization
            ? ['id' => $this->organization->id, 'name_en' => $this->organization->name_en, 'name_am' => $this->organization->name_am, 'code' => $this->organization->code]
            : null,
        );

        return [
            'id'                   => $this->id,
            'code'                 => $this->code,
            'name_en'              => $this->name_en,
            'name_am'              => $this->name_am,
            'organization_id'      => $this->organization_id,
            'organization'         => $org,
            'assigned_scope_type'  => $this->assigned_scope_type ?? 'self',
            'contact_person'       => $this->contact_person,
            'phone_number'         => $this->phone_number,
            'email'                => $this->email,
            'location'             => $this->location,
            'is_active'            => $this->is_active,
            'created_at'           => $this->created_at?->toISOString(),
            'updated_at'           => $this->updated_at?->toISOString(),
            'deleted_at'           => $this->deleted_at?->toISOString(),
            'can'                  => [
                'update'            => $user?->can('update', $this->resource) ?? false,
                'archive'           => $user?->can('archive', $this->resource) ?? false,
                'restore'           => $user?->can('restore', $this->resource) ?? false,
                'updateInstitution' => $user?->can('updateInstitution', $this->resource) ?? false,
            ],
        ];
    }
}
