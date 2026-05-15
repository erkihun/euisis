<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\SystemSettings\SystemSettingsService;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Throwable;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();
        $settings = $this->publicSettings();
        $defaultLocale = (string) ($settings['localization.default_locale'] ?? config('app.locale', 'en'));

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user ? [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'status'            => $user->status,
                    'profile_photo_url' => $user->profilePhotoUrl(),
                    'initials'          => $user->initials(),
                ] : null,
                'roles' => $user ? $user->getRoleNames()->toArray() : [],
                'permissions' => $user ? $user->getAllPermissions()->pluck('name')->toArray() : [],
                'isSuperAdmin' => $user?->hasRole('Super Admin') ?? false,
            ],

            'locale' => session('locale', $defaultLocale),
            'settings' => $settings,
            'flash' => [
                // Individual-key form (preferred) — set via session('success'), etc.
                'success' => session('success'),
                'error'   => session('error'),
                'warning' => session('warning'),
                'info'    => session('info'),
                // Single-message fallback — set via session()->flash('flash.message', …)
                'message' => session('flash.message'),
                'type'    => session('flash.type'),
            ],
        ];
    }

    private function publicSettings(): array
    {
        try {
            return app(SystemSettingsService::class)->getPublicSettings();
        } catch (Throwable) {
            return [];
        }
    }
}
