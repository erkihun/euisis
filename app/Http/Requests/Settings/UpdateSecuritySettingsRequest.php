<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSecuritySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('system-settings.manageSecurity') ?? false;
    }

    public function rules(): array
    {
        return [
            'password_min_length' => ['required', 'integer', 'min:8', 'max:64'],
            'session_timeout_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'max_upload_size_mb' => ['required', 'integer', 'min:1', 'max:50'],
            'password_complexity_enabled' => ['required', 'boolean'],
            'max_login_attempts' => ['required', 'integer', 'min:1', 'max:50'],
            'lockout_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'password_expiry_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'require_mfa_for_admins' => ['required', 'boolean'],
            'force_https' => ['required', 'boolean'],
            'maintenance_banner_enabled' => ['required', 'boolean'],
            'maintenance_banner_message_en' => ['nullable', 'string', 'max:2000'],
            'maintenance_banner_message_am' => ['nullable', 'string', 'max:2000'],
            'allowed_file_types' => ['required', 'array', 'min:1'],
            'allowed_file_types.*' => ['string', 'max:16', 'regex:/^[a-z0-9]+$/i'],
            'allowed_upload_mime_types' => ['required', 'array', 'min:1'],
            'allowed_upload_mime_types.*' => ['string', 'max:100'],
            'audit_retention_days' => ['required', 'integer', 'min:30', 'max:3650'],
            'sensitive_export_requires_reason' => ['required', 'boolean'],
            'api_rate_limit_per_minute' => ['required', 'integer', 'min:30', 'max:10000'],
            'verification_rate_limit_per_minute' => ['required', 'integer', 'min:30', 'max:10000'],
        ];
    }
}
