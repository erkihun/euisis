<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportingLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source_type' => str_contains($this->getTable(), 'office') ? 'institution_office' : 'organization_unit',
            'source' => $this->whenLoaded('sourceOffice', fn () => [
                'id' => $this->sourceOffice->id,
                'name_en' => $this->sourceOffice->name_en,
                'code' => $this->sourceOffice->office_code,
            ], fn () => $this->whenLoaded('sourceUnit', fn () => [
                'id' => $this->sourceUnit->id,
                'name_en' => $this->sourceUnit->name_en,
                'code' => $this->sourceUnit->code,
            ])),
            'target_type' => $this->target_type?->value ?? (string) $this->target_type,
            'target_id' => $this->target_id,
            'relationship_type' => $this->relationship_type?->value ?? (string) $this->relationship_type,
            'relationship_label' => $this->relationship_type?->label() ?? (string) $this->relationship_type,
            'is_primary' => (bool) $this->is_primary,
            'status' => $this->status?->value ?? (string) $this->status,
            'effective_from' => $this->effective_from?->toDateString(),
            'effective_to' => $this->effective_to?->toDateString(),
        ];
    }
}
