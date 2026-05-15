<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HierarchyTreeNodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'organization_id' => $this['organization_id'],
            'edge_id' => $this['edge_id'],
            'parent_organization_id' => $this['parent_organization_id'],
            'code' => $this['code'],
            'name_en' => $this['name_en'],
            'name_am' => $this['name_am'],
            'organization_type' => $this['organization_type'],
            'status' => $this['status'],
            'logo_url' => $this['logo_url'],
            'depth' => $this['depth'],
            'child_count' => $this['child_count'],
            'relationship_type' => $this['relationship_type'],
            'effective_from' => $this['effective_from'],
            'effective_to' => $this['effective_to'],
            'can' => $this['can'],
            'children' => collect($this['children'] ?? [])
                ->map(fn (array $child): array => (new self($child))->toArray($request))
                ->values()
                ->all(),
        ];
    }
}
