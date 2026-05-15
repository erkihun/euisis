<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserOrganizationScopes\StoreUserOrganizationScopeRequest;
use App\Http\Requests\UserOrganizationScopes\UpdateUserOrganizationScopeRequest;
use App\Http\Resources\UserOrganizationScopeResource;
use App\Models\User;
use App\Models\UserOrganizationScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserOrganizationScopeController extends Controller
{
    public function index(User $user): JsonResponse
    {
        $this->authorize('viewAny', UserOrganizationScope::class);

        $scopes = $user->organizationScopes()->with('organization')->get();

        return response()->json(UserOrganizationScopeResource::collection($scopes));
    }

    public function store(
        StoreUserOrganizationScopeRequest $request,
        User $user,
        WriteAuditLogAction $auditLog,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = Auth::user();

        $scope = $user->organizationScopes()->create([
            ...$request->validated(),
            'assigned_by' => $actor->getKey(),
        ]);

        $auditLog->execute(
            AuditEventType::UserOrganizationScopeAssigned,
            $actor,
            $scope,
            $request->validated('organization_id'),
            null,
            $request->validated(),
            null,
            $request,
        );

        return back()->with('flash', ['message' => __('users.organization_scope_assigned'), 'type' => 'success']);
    }

    public function update(
        UpdateUserOrganizationScopeRequest $request,
        User $user,
        UserOrganizationScope $scope,
        WriteAuditLogAction $auditLog,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = Auth::user();

        $old = $scope->only(['organization_id', 'scope_type', 'effective_from', 'effective_to', 'is_active']);

        $scope->update($request->validated());

        $auditLog->execute(
            AuditEventType::UserOrganizationScopeUpdated,
            $actor,
            $scope,
            $scope->organization_id,
            $old,
            $request->validated(),
            null,
            $request,
        );

        return back()->with('flash', ['message' => __('users.organization_scope_updated'), 'type' => 'success']);
    }

    public function destroy(
        Request $request,
        User $user,
        UserOrganizationScope $scope,
        WriteAuditLogAction $auditLog,
    ): RedirectResponse {
        $this->authorize('delete', $scope);

        /** @var User $actor */
        $actor = Auth::user();

        $old = $scope->only(['organization_id', 'scope_type', 'effective_from', 'effective_to', 'is_active']);

        $scope->delete();

        $auditLog->execute(
            AuditEventType::UserOrganizationScopeRemoved,
            $actor,
            null,
            $old['organization_id'] ?? null,
            $old,
            null,
            null,
            $request,
        );

        return back()->with('flash', ['message' => __('users.organization_scope_removed'), 'type' => 'success']);
    }
}
