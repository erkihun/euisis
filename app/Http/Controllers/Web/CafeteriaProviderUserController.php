<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CafeteriaProvider;
use App\Models\CafeteriaProviderUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class CafeteriaProviderUserController extends Controller
{
    public function index(Request $request, CafeteriaProvider $provider): Response
    {
        $this->authorize('viewAny', [CafeteriaProviderUser::class, $provider]);

        $users = CafeteriaProviderUser::query()
            ->where('cafeteria_provider_id', $provider->id)
            ->withTrashed()
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Cafeteria/ProviderUsers/Index', [
            'provider' => [
                'id' => $provider->id,
                'code' => $provider->code,
                'name_en' => $provider->name_en,
                'name_am' => $provider->name_am,
            ],
            'providerUsers' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
            'can' => [
                'create' => $request->user()?->can('create', [CafeteriaProviderUser::class, $provider]) ?? false,
            ],
        ]);
    }

    public function create(Request $request, CafeteriaProvider $provider): Response
    {
        $this->authorize('create', [CafeteriaProviderUser::class, $provider]);

        return Inertia::render('Cafeteria/ProviderUsers/Create', [
            'provider' => [
                'id' => $provider->id,
                'code' => $provider->code,
                'name_en' => $provider->name_en,
                'name_am' => $provider->name_am,
            ],
        ]);
    }

    public function store(Request $request, CafeteriaProvider $provider): RedirectResponse
    {
        $this->authorize('create', [CafeteriaProviderUser::class, $provider]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:cafeteria_provider_users,email'],
            'username' => ['nullable', 'string', 'max:100', 'unique:cafeteria_provider_users,username', 'alpha_dash'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'password' => ['required', Password::min(8)],
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
            'portal_enabled' => ['boolean'],
            'must_change_password' => ['boolean'],
        ]);

        $this->requiresEmailOrUsername($validated);

        CafeteriaProviderUser::create([
            ...$validated,
            'cafeteria_provider_id' => $provider->id,
            'password' => Hash::make($validated['password']),
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('cafeteria.providers.users.index', $provider)
            ->with('success', __('cafeteria.providerUserSaved'));
    }

    public function edit(Request $request, CafeteriaProvider $provider, CafeteriaProviderUser $providerUser): Response
    {
        $this->authorize('update', [$providerUser, $provider]);
        $this->abortIfWrongProvider($provider, $providerUser);

        return Inertia::render('Cafeteria/ProviderUsers/Edit', [
            'provider' => [
                'id' => $provider->id,
                'code' => $provider->code,
                'name_en' => $provider->name_en,
                'name_am' => $provider->name_am,
            ],
            'providerUser' => [
                'id' => $providerUser->id,
                'name' => $providerUser->name,
                'email' => $providerUser->email,
                'username' => $providerUser->username,
                'status' => $providerUser->status,
                'portal_enabled' => $providerUser->portal_enabled,
                'must_change_password' => $providerUser->must_change_password,
            ],
        ]);
    }

    public function update(Request $request, CafeteriaProvider $provider, CafeteriaProviderUser $providerUser): RedirectResponse
    {
        $this->authorize('update', [$providerUser, $provider]);
        $this->abortIfWrongProvider($provider, $providerUser);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:cafeteria_provider_users,email,'.$providerUser->id],
            'username' => ['nullable', 'string', 'max:100', 'unique:cafeteria_provider_users,username,'.$providerUser->id, 'alpha_dash'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
            'portal_enabled' => ['boolean'],
            'must_change_password' => ['boolean'],
        ]);

        $this->requiresEmailOrUsername($validated);

        $providerUser->fill([
            ...$validated,
            'updated_by' => $request->user()?->id,
        ])->save();

        return back()->with('success', __('cafeteria.providerUserUpdated'));
    }

    public function resetPassword(Request $request, CafeteriaProvider $provider, CafeteriaProviderUser $providerUser): RedirectResponse
    {
        $this->authorize('resetPassword', [$providerUser, $provider]);
        $this->abortIfWrongProvider($provider, $providerUser);

        $validated = $request->validate([
            'password' => ['required', Password::min(8)],
        ]);

        $providerUser->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => true,
            'updated_by' => $request->user()?->id,
        ])->save();

        return back()->with('success', __('cafeteria.providerUserPasswordReset'));
    }

    public function suspend(Request $request, CafeteriaProvider $provider, CafeteriaProviderUser $providerUser): RedirectResponse
    {
        $this->authorize('suspend', [$providerUser, $provider]);
        $this->abortIfWrongProvider($provider, $providerUser);

        $validated = $request->validate([
            'suspension_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $providerUser->forceFill([
            'status' => 'suspended',
            'suspended_by' => $request->user()?->id,
            'suspended_at' => now(),
            'suspension_reason' => $validated['suspension_reason'] ?? null,
            'updated_by' => $request->user()?->id,
        ])->save();

        return back()->with('success', __('cafeteria.providerUserSuspended'));
    }

    public function activate(Request $request, CafeteriaProvider $provider, CafeteriaProviderUser $providerUser): RedirectResponse
    {
        $this->authorize('activate', [$providerUser, $provider]);
        $this->abortIfWrongProvider($provider, $providerUser);

        $providerUser->forceFill([
            'status' => 'active',
            'suspended_by' => null,
            'suspended_at' => null,
            'suspension_reason' => null,
            'updated_by' => $request->user()?->id,
        ])->save();

        return back()->with('success', __('cafeteria.providerUserActivated'));
    }

    public function destroy(Request $request, CafeteriaProvider $provider, CafeteriaProviderUser $providerUser): RedirectResponse
    {
        $this->authorize('delete', [$providerUser, $provider]);
        $this->abortIfWrongProvider($provider, $providerUser);

        $providerUser->delete();

        return redirect()
            ->route('cafeteria.providers.users.index', $provider)
            ->with('success', __('cafeteria.providerUserDeleted'));
    }

    private function abortIfWrongProvider(CafeteriaProvider $provider, CafeteriaProviderUser $providerUser): void
    {
        abort_if(
            $providerUser->cafeteria_provider_id !== $provider->id,
            404,
        );
    }

    private function requiresEmailOrUsername(array $validated): void
    {
        if (empty($validated['email']) && empty($validated['username'])) {
            abort(422, __('cafeteria.providerUserNeedsEmailOrUsername'));
        }
    }
}
