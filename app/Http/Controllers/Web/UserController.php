<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Users\AssignRolesAction;
use App\Actions\Users\CreateUserAction;
use App\Actions\Users\DeactivateUserAction;
use App\Actions\Users\RestoreUserAction;
use App\Actions\Users\UpdateUserAction;
use App\Actions\Users\UploadUserProfilePhotoAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRolesRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        /** @var User $actor */
        $actor = Auth::user();

        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'status' => $u->status,
                'phone_number' => $u->phone_number,
                'gender' => $u->gender,
                'last_login_at' => $u->last_login_at?->toDateTimeString(),
                'created_at' => $u->created_at?->toDateString(),
                'roles' => $u->roles->pluck('name')->toArray(),
                'profile_photo_url' => $u->profilePhotoUrl(),
                'can' => [
                    'update' => $actor->can('update', $u),
                    'archive' => $actor->can('archive', $u),
                    'restore' => $actor->can('restore', $u),
                    'assignRoles' => $actor->can('assignRoles', $u),
                    'delete' => $actor->can('delete', $u),
                ],
            ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'can' => [
                'create' => $actor->can('create', User::class),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create', [
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'statusOptions' => ['active', 'inactive'],
            'organizations' => Organization::query()
                ->where('status', 'active')
                ->orderBy('name_en')
                ->get(['id', 'name_en', 'name_am']),
        ]);
    }

    public function store(UserStoreRequest $request, CreateUserAction $action, UploadUserProfilePhotoAction $photoAction): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['profile_photo']);

        /** @var User $actor */
        $actor = $request->user();

        $user = $action->execute($validated, $actor);

        if ($request->hasFile('profile_photo')) {
            $path = $photoAction->execute($user, $request->file('profile_photo'), $actor, $request);
            $user->update(['profile_photo_path' => $path]);
        }

        return to_route('users.index')
            ->with('flash', ['message' => __('users.created'), 'type' => 'success']);
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $user->load('organizationScopes.organization');

        /** @var User $actor */
        $actor = Auth::user();

        return Inertia::render('Users/Edit', [
            'user' => array_merge(
                $user->only(['id', 'name', 'email', 'status', 'national_id', 'phone_number', 'gender']),
                [
                    'profile_photo_url' => $user->profilePhotoUrl(),
                    'organization_scopes' => $user->organizationScopes->map(fn ($s) => [
                        'id' => $s->id,
                        'organization' => $s->organization ? [
                            'id' => $s->organization->id,
                            'name_en' => $s->organization->name_en,
                            'name_am' => $s->organization->name_am,
                        ] : null,
                        'scope_type' => $s->scope_type?->value ?? $s->scope_type,
                        'effective_from' => $s->effective_from?->toDateString(),
                        'effective_to' => $s->effective_to?->toDateString(),
                        'is_active' => $s->is_active,
                    ])->values()->toArray(),
                ],
            ),
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'userRoles' => $user->getRoleNames()->toArray(),
            'organizations' => Organization::query()
                ->where('status', 'active')
                ->orderBy('name_en')
                ->get(['id', 'name_en', 'name_am']),
            'can' => [
                'assignOrganizationScopes' => $actor->can('users.assignOrganizationScopes'),
            ],
        ]);
    }

    public function update(UserUpdateRequest $request, User $user, UpdateUserAction $action, UploadUserProfilePhotoAction $photoAction): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['profile_photo']);

        /** @var User $actor */
        $actor = $request->user();

        $action->execute($validated, $user, $actor);

        if ($request->hasFile('profile_photo')) {
            $path = $photoAction->execute($user, $request->file('profile_photo'), $actor, $request);
            $user->update(['profile_photo_path' => $path]);
        }

        return to_route('users.index')
            ->with('flash', ['message' => __('users.updated'), 'type' => 'success']);
    }

    public function deactivate(Request $request, User $user, DeactivateUserAction $action): RedirectResponse
    {
        $this->authorize('archive', $user);

        /** @var User $actor */
        $actor = $request->user();

        $action->execute($user, $actor);

        return to_route('users.index')
            ->with('flash', ['message' => __('users.deactivated'), 'type' => 'success']);
    }

    public function restore(Request $request, User $user, RestoreUserAction $action): RedirectResponse
    {
        $this->authorize('restore', $user);

        /** @var User $actor */
        $actor = $request->user();

        $action->execute($user, $actor);

        return to_route('users.index')
            ->with('flash', ['message' => __('users.restored'), 'type' => 'success']);
    }

    public function assignRoles(AssignRolesRequest $request, User $user, AssignRolesAction $action): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $action->execute($user, $request->validated('roles', []), $actor);

        return to_route('users.index')
            ->with('flash', ['message' => __('users.roles_updated'), 'type' => 'success']);
    }
}
