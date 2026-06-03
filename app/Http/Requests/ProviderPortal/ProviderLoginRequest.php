<?php

declare(strict_types=1);

namespace App\Http\Requests\ProviderPortal;

use App\Models\ProviderUser;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Handles provider portal login against the dedicated provider guard.
 */
class ProviderLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifier = $this->string('identifier')->toString();
        $password = $this->string('password')->toString();
        $remember = $this->boolean('remember');

        $providerUser = $this->resolveProviderUser($identifier);

        if ($providerUser === null || ! password_verify($password, $providerUser->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'identifier' => __('provider-portal.login_failed'),
            ]);
        }

        if (! $providerUser->canLogin()) {
            RateLimiter::hit($this->throttleKey());

            $errorKey = ! $providerUser->isPortalEnabled()
                ? 'provider-portal.portal_disabled'
                : 'provider-portal.access_denied';

            throw ValidationException::withMessages([
                'identifier' => __($errorKey),
            ]);
        }

        Auth::guard('provider')->login($providerUser, $remember);

        $providerUser->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $this->ip(),
        ])->saveQuietly();

        RateLimiter::clear($this->throttleKey());
    }

    private function resolveProviderUser(string $identifier): ?ProviderUser
    {
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return ProviderUser::with('provider.services.serviceType')
            ->where($field, $identifier)
            ->first();
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'identifier' => __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($this->throttleKey()),
                'minutes' => ceil(RateLimiter::availableIn($this->throttleKey()) / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('identifier')->toString()).'|'.$this->ip());
    }
}
