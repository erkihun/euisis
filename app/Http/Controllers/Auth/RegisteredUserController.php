<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view, or redirect to /login when public
     * self-service registration is disabled.
     */
    public function create(): Response|RedirectResponse
    {
        if ($redirect = $this->disabledRedirect()) {
            return $redirect;
        }

        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->disabledRedirect()) {
            return $redirect;
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Returns a redirect to /login when public self-service registration is
     * disabled (REGISTRATION_ENABLED env / config('security.registration_enabled')),
     * or null when registration is on and processing should continue.
     */
    private function disabledRedirect(): ?RedirectResponse
    {
        if (config('security.registration_enabled', false)) {
            return null;
        }

        return redirect()->route('login')
            ->with('status', __('security.registration_disabled'));
    }
}
