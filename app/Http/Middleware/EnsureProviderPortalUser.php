<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderPortalUser
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\ProviderUser|null $user */
        $user = auth('provider')->user();

        if ($user === null) {
            return redirect()->route('provider.portal.login');
        }

        if (! $user->canLogin()) {
            auth('provider')->logout();

            return redirect()->route('provider.portal.login')
                ->withErrors(['identifier' => __('provider-portal.access_denied')]);
        }

        return $next($request);
    }
}
