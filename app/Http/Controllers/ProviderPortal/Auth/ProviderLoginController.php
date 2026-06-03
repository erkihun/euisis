<?php

declare(strict_types=1);

namespace App\Http\Controllers\ProviderPortal\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderPortal\ProviderLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProviderLoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('ProviderPortal/Auth/Login');
    }

    public function store(ProviderLoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('provider.portal.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('provider')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('provider.portal.login');
    }
}
