<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Permission */
class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'label_en' => $this->label_en,
            'label_am' => $this->label_am,
            'description_en' => $this->description_en,
            'description_am' => $this->description_am,
            'group' => $this->group,
            'sort_order' => $this->sort_order,
            'is_system' => (bool) $this->is_system,
            'roles_count' => $this->whenCounted('roles'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
