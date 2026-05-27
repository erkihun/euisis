<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // ── Anti-clickjacking ────────────────────────────────────────────────
        // DENY prevents framing by any origin, including same-origin.
        // frame-ancestors 'none' in CSP supersedes this in modern browsers;
        // both are set for defence-in-depth with legacy browser support.
        $response->headers->set('X-Frame-Options', 'DENY');

        // ── MIME-type sniffing prevention ────────────────────────────────────
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // ── Referrer control ─────────────────────────────────────────────────
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ── Browser feature restrictions ─────────────────────────────────────
        $response->headers->set(
            'Permissions-Policy',
            'camera=(self), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()'
        );

        // ── Cross-domain policy files ─────────────────────────────────────────
        // Prevents Adobe Flash / PDF plugins from loading cross-domain policies.
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // ── Cross-Origin isolation ────────────────────────────────────────────
        // COOP prevents cross-origin windows from accessing this window object.
        // CORP prevents other origins from loading this resource via <img>/<script>.
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        // ── Content Security Policy ───────────────────────────────────────────
        // Key directives:
        //   frame-ancestors 'none' — prevents embedding in any frame (clickjacking)
        //   object-src 'none'      — blocks Flash/ActiveX/plugins
        //   base-uri 'self'        — prevents base-tag injection
        //   form-action 'self'     — prevents cross-origin form submission
        //   upgrade-insecure-requests — forces HTTP sub-resources to HTTPS
        //
        // NOTE: script-src includes 'unsafe-inline' for compatibility with
        // Vite HMR in development and inline event handlers. In a future
        // hardening pass, replace with nonce-based CSP using Laravel's built-in
        // Vite::useCspNonce() integration.
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            "connect-src 'self' ws: wss:",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]));

        // ── HSTS (HTTPS only) ─────────────────────────────────────────────────
        // Only sent when the request was served over HTTPS to avoid accidentally
        // pinning a site that later loses its certificate, or breaking plain-HTTP
        // local development.  63 072 000 s = 2 years.
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=63072000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
