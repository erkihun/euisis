<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ErrorLoggingService
{
    /**
     * Fields whose values must never appear in logs.
     */
    private const REDACTED_FIELDS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'access_token',
        'refresh_token',
        'api_token',
        'authorization',
        'secret',
        'private_key',
        'signature',
        'qr_token',
        'verification_token',
        'card_token',
        'national_id',
        'document_path',
        '_token',
    ];

    /**
     * Log an unexpected throwable with full context, sanitised input, and a
     * correlation ID. Returns the error ID so it can be surfaced to the user.
     */
    public function log(Throwable $e, ?Request $request = null): string
    {
        $errorId = $this->generateErrorId();
        $correlationId = $this->resolveCorrelationId($request);

        $context = [
            'error_id' => $errorId,
            'correlation_id' => $correlationId,
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'timestamp' => now()->toIso8601String(),
        ];

        if ($request !== null) {
            $context['request'] = [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => optional($request->route())->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => optional($request->user())->id,
                'input' => $this->sanitizeInput($request->all()),
            ];
        }

        Log::error("[{$errorId}] Unhandled exception: {$e->getMessage()}", $context);

        return $errorId;
    }

    /**
     * Sanitize request input so sensitive fields are redacted before logging.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function sanitizeInput(array $input): array
    {
        $redacted = array_map('strtolower', self::REDACTED_FIELDS);

        return $this->redactRecursive($input, $redacted);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  string[]  $redacted
     * @return array<string, mixed>
     */
    private function redactRecursive(array $data, array $redacted): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), $redacted, true)) {
                $result[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $result[$key] = $this->redactRecursive($value, $redacted);
            } elseif (is_string($value) && strlen($value) > 1000) {
                // Truncate large payloads (base64 blobs, etc.).
                $result[$key] = '[TRUNCATED:'.strlen($value).'chars]';
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function generateErrorId(): string
    {
        return 'ERR-'.strtoupper(Str::random(12));
    }

    private function resolveCorrelationId(?Request $request): string
    {
        if ($request === null) {
            return app()->bound('correlation_id')
                ? (string) app('correlation_id')
                : 'N/A';
        }

        return $request->attributes->get('correlation_id', (string) app()->bound('correlation_id')
            ? app('correlation_id')
            : 'N/A');
    }
}
