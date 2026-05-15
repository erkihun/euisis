<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_number' => $this->employee_number,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status?->value ?? $this->status,
            'duplicate_flags_count' => $this->whenCounted('employeeDuplicateFlags'),
            'current_assignment' => $this->whenLoaded('currentAssignment', fn (): ?array => $this->currentAssignment ? [
                'id' => $this->currentAssignment->id,
                'assignment_status' => $this->currentAssignment->assignment_status?->value ?? $this->currentAssignment->assignment_status,
                'effective_from' => $this->currentAssignment->effective_from?->toDateString(),
                'effective_to' => $this->currentAssignment->effective_to?->toDateString(),
                'organization' => $this->currentAssignment->organization ? [
                    'id' => $this->currentAssignment->organization->id,
                    'name_en' => $this->currentAssignment->organization->name_en,
                ] : null,
                'position' => $this->currentAssignment->position ? [
                    'id' => $this->currentAssignment->position->id,
                    'title_en' => $this->currentAssignment->position->title_en,
                ] : null,
            ] : null),
        ];
    }
}
