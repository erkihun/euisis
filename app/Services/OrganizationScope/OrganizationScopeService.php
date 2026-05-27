<?php

declare(strict_types=1);

namespace App\Services\OrganizationScope;

use App\Enums\HierarchyVersionStatus;
use App\Enums\OrganizationScopeType;
use App\Models\Employee;
use App\Models\HierarchyVersion;
use App\Models\Organization;
use App\Models\OrganizationClosurePath;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class OrganizationScopeService
{
    /** @var array<string, Collection> */
    private array $requestCache = [];

    public function canAccessOrganization(User $user, ?string $organizationId): bool
    {
        if ($organizationId === null) {
            return false;
        }

        if ($user->hasRole('Super Admin') || $user->hasRole('City Admin')) {
            return true;
        }

        $hasAnyScopeRecord = $user->organizationScopes()->exists();

        if (! $hasAnyScopeRecord) {
            return true;
        }

        return $this->accessibleOrganizationIds($user)->contains($organizationId);
    }

    public function canAccessEmployee(User $user, Employee $employee): bool
    {
        return $this->canAccessOrganization($user, $employee->currentAssignment?->organization_id);
    }

    public function clearCache(?User $user = null): void
    {
        if ($user !== null) {
            foreach (array_keys($this->requestCache) as $key) {
                if (str_starts_with($key, "org_scope.{$user->getKey()}.")) {
                    unset($this->requestCache[$key]);
                }
            }
        } else {
            $this->requestCache = [];
        }
    }

    public function descendantsForOrganization(string $organizationId, ?string $hierarchyVersionId = null): Collection
    {
        $query = OrganizationClosurePath::query()
            ->where('ancestor_organization_id', $organizationId);

        if ($hierarchyVersionId !== null) {
            $query->where('hierarchy_version_id', $hierarchyVersionId);
        }

        return $query
            ->orderBy('depth')
            ->get(['descendant_organization_id', 'depth']);
    }

    /**
     * Flat depth-first traversal for the Organizations index page.
     * Returns every node with depth, parent_id, and children_count so the
     * frontend can render an indented hierarchy without recursive calls.
     * When $allowedOrgIds is provided, only nodes within that set are included.
     *
     * @param  string[]|null  $allowedOrgIds  null means no restriction
     */
    public function buildFlatTreeForIndex(?HierarchyVersion $version, ?array $allowedOrgIds = null): array
    {
        if ($version === null) {
            return [];
        }

        $edges = $version->edges()
            ->get(['parent_organization_id', 'child_organization_id']);

        if ($edges->isEmpty()) {
            return [];
        }

        $allOrgIds = $edges->pluck('parent_organization_id')
            ->merge($edges->pluck('child_organization_id'))
            ->unique()
            ->values();

        if ($allowedOrgIds !== null) {
            $allOrgIds = $allOrgIds->intersect($allowedOrgIds)->values();
        }

        $organizations = Organization::query()
            ->whereIn('id', $allOrgIds)
            ->with('type:id,name_en,name_am,code')
            ->get()
            ->keyBy('id');

        $childrenByParent = $edges->groupBy('parent_organization_id');
        $childIds = $edges->pluck('child_organization_id')->unique()->values();
        $rootIds = $allOrgIds->diff($childIds)->values();

        $flat = [];

        $buildFlat = function (string $orgId, int $depth, ?string $parentId) use (
            &$buildFlat, &$flat, $organizations, $childrenByParent
        ): void {
            $org = $organizations->get($orgId);
            if ($org === null) {
                return;
            }

            $childEdges = $childrenByParent->get($orgId, collect());

            $flat[] = [
                'id' => $org->id,
                'code' => $org->code,
                'name_en' => $org->name_en,
                'name_am' => $org->name_am,
                'status' => $org->status instanceof \BackedEnum ? $org->status->value : (string) $org->status,
                'effective_from' => $org->effective_from?->toDateString(),
                'effective_to' => $org->effective_to?->toDateString(),
                'depth' => $depth,
                'parent_id' => $parentId,
                'children_count' => $childEdges->count(),
                'type' => $org->type ? [
                    'name_en' => $org->type->name_en,
                    'name_am' => $org->type->name_am,
                    'code' => $org->type->code,
                ] : null,
                'branding_primary_color' => $org->branding_primary_color,
                'logo_url' => $org->logo_url,
            ];

            foreach ($childEdges as $edge) {
                $buildFlat($edge->child_organization_id, $depth + 1, $orgId);
            }
        };

        foreach ($rootIds as $rootId) {
            $buildFlat($rootId, 0, null);
        }

        return $flat;
    }

    public function buildVersionTree(HierarchyVersion $version, ?User $user = null): array
    {
        $edges = $version->edges()
            ->with([
                'parentOrganization:id,organization_type_id,code,name_en,name_am,status,logo_path',
                'parentOrganization.type:id,code,name_en,name_am',
                'childOrganization:id,organization_type_id,code,name_en,name_am,status,logo_path',
                'childOrganization.type:id,code,name_en,name_am',
            ])
            ->get();

        if ($edges->isEmpty()) {
            return [];
        }

        $childIds = $edges->pluck('child_organization_id')->unique();
        $parentIds = $edges->pluck('parent_organization_id')->unique();
        $rootIds = $parentIds->diff($childIds)->values();

        $childrenByParent = $edges->groupBy('parent_organization_id');

        $buildNode = function ($edge, int $depth) use (&$buildNode, $childrenByParent, $user, $version): array {
            $organization = $edge->childOrganization;

            if ($organization === null) {
                return [];
            }

            $children = $childrenByParent
                ->get($organization->id, collect())
                ->map(fn ($childEdge) => $buildNode($childEdge, $depth + 1))
                ->filter()
                ->values()
                ->all();

            return [
                'organization_id' => $organization->id,
                'edge_id' => $edge->id,
                'parent_organization_id' => $edge->parent_organization_id,
                'code' => $organization->code,
                'name_en' => $organization->name_en,
                'name_am' => $organization->name_am,
                'organization_type' => $organization->type ? [
                    'code' => $organization->type->code,
                    'name_en' => $organization->type->name_en,
                    'name_am' => $organization->type->name_am,
                ] : null,
                'status' => $organization->status instanceof \BackedEnum ? $organization->status->value : (string) $organization->status,
                'logo_url' => $organization->logo_url,
                'depth' => $depth,
                'child_count' => count($children),
                'relationship_type' => $edge->relationship_type instanceof \BackedEnum ? $edge->relationship_type->value : (string) $edge->relationship_type,
                'effective_from' => $edge->effective_from?->toDateString(),
                'effective_to' => $edge->effective_to?->toDateString(),
                'can' => [
                    'edit' => ($user?->can('update', $edge) ?? false)
                        && $version->status === HierarchyVersionStatus::Draft,
                    'remove' => ($user?->can('delete', $edge) ?? false)
                        && $version->status === HierarchyVersionStatus::Draft,
                    'addChild' => ($user?->can('organization-edges.create') ?? false)
                        && ($user?->can('hierarchy-versions.manageTree') ?? false)
                        && $version->status === HierarchyVersionStatus::Draft,
                ],
                'children' => $children,
            ];
        };

        return $rootIds
            ->map(function (string $rootId) use ($childrenByParent, $user, $version, $buildNode): array {
                $rootOrganization = Organization::query()
                    ->with('type:id,code,name_en,name_am')
                    ->find($rootId, ['id', 'organization_type_id', 'code', 'name_en', 'name_am', 'status', 'logo_path']);

                if ($rootOrganization === null) {
                    return [];
                }

                $children = $childrenByParent
                    ->get($rootId, collect())
                    ->map(fn ($edge) => $buildNode($edge, 1))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'organization_id' => $rootOrganization->id,
                    'edge_id' => null,
                    'parent_organization_id' => null,
                    'code' => $rootOrganization->code,
                    'name_en' => $rootOrganization->name_en,
                    'name_am' => $rootOrganization->name_am,
                    'organization_type' => $rootOrganization->type ? [
                        'code' => $rootOrganization->type->code,
                        'name_en' => $rootOrganization->type->name_en,
                        'name_am' => $rootOrganization->type->name_am,
                    ] : null,
                    'status' => $rootOrganization->status instanceof \BackedEnum ? $rootOrganization->status->value : (string) $rootOrganization->status,
                    'logo_url' => $rootOrganization->logo_url,
                    'depth' => 0,
                    'child_count' => count($children),
                    'relationship_type' => null,
                    'effective_from' => null,
                    'effective_to' => null,
                    'can' => [
                        'edit' => false,
                        'remove' => false,
                        'addChild' => ($user?->can('organization-edges.create') ?? false)
                            && ($user?->can('hierarchy-versions.manageTree') ?? false)
                            && $version->status === HierarchyVersionStatus::Draft,
                    ],
                    'children' => $children,
                ];
            })
            ->filter(fn (array $node) => $node !== [])
            ->values()
            ->all();
    }

    public function summarizeVersionTree(array $tree): array
    {
        $summary = [
            'total_organizations' => 0,
            'total_relations' => 0,
            'root_nodes' => count($tree),
            'max_depth' => 0,
        ];

        $walk = function (array $nodes) use (&$walk, &$summary): void {
            foreach ($nodes as $node) {
                $summary['total_organizations']++;
                $summary['max_depth'] = max($summary['max_depth'], (int) ($node['depth'] ?? 0));

                if (($node['edge_id'] ?? null) !== null) {
                    $summary['total_relations']++;
                }

                $walk($node['children'] ?? []);
            }
        };

        $walk($tree);

        return $summary;
    }

    public function accessibleOrganizationIds(User $user): Collection
    {
        if ($user->hasRole('Super Admin') || $user->hasRole('City Admin')) {
            return Organization::query()->pluck('id');
        }

        $publishedVersionId = $this->resolvePublishedVersionId() ?? 'none';
        $cacheKey = "org_scope.{$user->getKey()}.{$publishedVersionId}";

        if (isset($this->requestCache[$cacheKey])) {
            return $this->requestCache[$cacheKey];
        }

        $scopes = $user->organizationScopes()->active()->get();

        $ids = collect();

        foreach ($scopes as $scope) {
            if ($scope->scope_type === OrganizationScopeType::Citywide) {
                $result = Organization::query()->pluck('id');
                $this->requestCache[$cacheKey] = $result;

                return $result;
            }

            if ($scope->organization_id === null) {
                continue;
            }

            if ($scope->scope_type === OrganizationScopeType::Self) {
                $ids->push($scope->organization_id);

                continue;
            }

            if ($scope->scope_type === OrganizationScopeType::Subtree) {
                if ($publishedVersionId === 'none') {
                    Log::warning('OrganizationScopeService: no published hierarchy version found; subtree scope falls back to assigned organization only.', [
                        'user_id' => $user->getKey(),
                        'organization_id' => $scope->organization_id,
                    ]);
                    $ids->push($scope->organization_id);

                    continue;
                }

                $ids = $ids->merge(
                    OrganizationClosurePath::query()
                        ->where('hierarchy_version_id', $publishedVersionId)
                        ->where('ancestor_organization_id', $scope->organization_id)
                        ->pluck('descendant_organization_id')
                );
            }
        }

        $result = $ids->unique()->values();
        $this->requestCache[$cacheKey] = $result;

        return $result;
    }

    private function resolvePublishedVersionId(): ?string
    {
        return HierarchyVersion::query()
            ->where('status', HierarchyVersionStatus::Published->value)
            ->latest('approval_date')
            ->value('id');
    }
}
