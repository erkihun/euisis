<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'job_position_code' => $this->job_position_code,
            'title_en' => $this->title_en,
            'title_am' => $this->title_am,
            'description_en' => $this->description_en,
            'description_am' => $this->description_am,
            'organization_id' => $this->organization_id,
            'organization' => $this->organization ? [
                'id' => $this->organization->id,
                'name_en' => $this->organization->name_en,
            ] : null,
            'organization_unit_id' => $this->organization_unit_id,
            'organization_unit' => $this->organizationUnit ? [
                'id' => $this->organizationUnit->id,
                'name_en' => $this->organizationUnit->name_en,
                'code' => $this->organizationUnit->code,
            ] : null,
            'occupation_id' => $this->occupation_id,
            'occupation' => $this->occupation ? [
                'id' => $this->occupation->id,
                'isco_code' => $this->occupation->isco_code,
                'name_en' => $this->occupation->name_en,
            ] : null,
            'grade_level' => $this->grade_level,
            'job_family' => $this->job_family,
            'is_active' => $this->is_active,
            'effective_from' => $this->effective_from?->toDateString(),
            'effective_to' => $this->effective_to?->toDateString(),
            'assignments_count' => $this->whenCounted('assignments'),
            'can' => [
                'view' => $user?->can('view', $this->resource) ?? false,
                'update' => $user?->can('update', $this->resource) ?? false,
                'archive' => $user?->can('archive', $this->resource) ?? false,
                'restore' => $user?->can('restore', $this->resource) ?? false,
            ],
        ];
    }
}
