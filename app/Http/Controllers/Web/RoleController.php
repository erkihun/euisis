<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private readonly WriteAuditLogAction $writeAuditLogAction) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Role::class);

        /** @var User $actor */
        $actor = Auth::user();

        $roles = Role::query()
            ->withCount('users')
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'users_count' => $r->users_count,
                'permissions' => $r->permissions->pluck('name')->toArray(),
                'is_super_admin' => $r->name === 'Super Admin',
                'can' => [
                    'update' => $actor?->can('update', $r) ?? false,
                    'delete' => $actor?->can('delete', $r) ?? false,
                    'assignPermissions' => $actor?->can('assignPermissions', $r) ?? false,
                ],
            ]);

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'can' => [
                'create' => $actor?->can('create', Role::class) ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Role::class);

        return Inertia::render('Roles/Create', [
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function store(RoleStoreRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
            $role->syncPermissions($request->validated('permissions', []));

            $this->writeAuditLogAction->execute(
                AuditEventType::RoleCreated,
                $request->user(),
                null,
                null,
                newValues: ['name' => $role->name, 'permissions' => $request->validated('permissions', [])],
            );
        });

        return to_route('roles.index')
            ->with('flash', ['message' => 'Role created.', 'type' => 'success']);
    }

    public function edit(Role $role): Response
    {
        $this->authorize('update', $role);

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ],
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function update(RoleUpdateRequest $request, Role $role): RedirectResponse
    {
        DB::transaction(function () use ($request, $role): void {
            $oldPermissions = $role->permissions->pluck('name')->toArray();
            $role->update(['name' => $request->validated('name')]);
            $role->syncPermissions($request->validated('permissions', []));

            $this->writeAuditLogAction->execute(
                AuditEventType::RoleUpdated,
                $request->user(),
                null,
                null,
                oldValues: ['name' => $role->getOriginal('name'), 'permissions' => $oldPermissions],
                newValues: ['name' => $role->name, 'permissions' => $request->validated('permissions', [])],
            );
        });

        return to_route('roles.index')
            ->with('flash', ['message' => 'Role updated.', 'type' => 'success']);
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        DB::transaction(function () use ($request, $role): void {
            $this->writeAuditLogAction->execute(
                AuditEventType::RoleDeleted,
                $request->user(),
                null,
                null,
                oldValues: ['name' => $role->name],
            );
            $role->delete();
        });

        return to_route('roles.index')
            ->with('flash', ['message' => 'Role deleted.', 'type' => 'success']);
    }

    private function groupedPermissions(): array
    {
        return Permission::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'label_en', 'label_am', 'description_en', 'description_am', 'group', 'sort_order', 'is_system'])
            ->groupBy(fn ($p) => $p->group ?? explode('.', $p->name)[0])
            ->map(fn ($group) => $group->map(fn ($p) => [
                'name' => $p->name,
                'label_en' => $p->label_en,
                'label_am' => $p->label_am,
                'description_en' => $p->description_en,
                'description_am' => $p->description_am,
                'is_system' => (bool) $p->is_system,
            ])->values()->toArray())
            ->toArray();
    }
}
