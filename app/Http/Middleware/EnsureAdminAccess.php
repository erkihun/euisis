<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks portal-only users (user_type = 'provider') from the main admin area.
 * Staff and super-admin users pass through; provider users are redirected to
 * their portal dashboard.
 */
class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->user_type === 'provider') {
            return redirect()->route('provider.portal.dashboard');
        }

        // Deny inactive/suspended admin accounts even if they hold a valid session.
        if ($user !== null && method_exists($user, 'isActive') && ! $user->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => __('auth.account_inactive')]);
        }

        return $next($request);
    }
}
