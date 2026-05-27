<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\CalendarSystem;
use App\Models\User;
use App\Services\Calendar\CalendarService;
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

        $locale = session('locale', $defaultLocale);
        $calendarMode = (string) ($settings['localization.calendar_system_mode'] ?? 'locale_based');
        $calendarSystem = $this->resolveCalendarSystem($locale, $calendarMode);

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

            'locale' => $locale,
            'calendar' => [
                'system' => $calendarSystem->value,
                'mode'   => $calendarMode,
            ],
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

    private function resolveCalendarSystem(string $locale, string $mode): CalendarSystem
    {
        return match ($mode) {
            'gregorian_only' => CalendarSystem::Gregorian,
            'ethiopian_only' => CalendarSystem::Ethiopian,
            default          => app(CalendarService::class)->calendarSystemForLocale($locale),
        };
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
