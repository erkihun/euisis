<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmailSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('system-settings.manageEmail') ?? false;
    }

    public function rules(): array
    {
        return [
            'mail_mailer' => ['required', Rule::in(['smtp', 'log', 'sendmail', 'mailgun', 'ses'])],
            'mail_host' => ['nullable', 'string', 'max:160'],
            'mail_port' => ['nullable', 'integer', 'between:1,65535'],
            'mail_encryption' => ['nullable', Rule::in(['tls', 'ssl', 'none'])],
            'mail_from_address' => ['nullable', 'email', 'max:160'],
            'mail_from_name' => ['nullable', 'string', 'max:160'],
            'mail_username' => ['nullable', 'string', 'max:200'],
            'mail_password' => ['nullable', 'string', 'max:400'],
            'email_test_recipient' => ['nullable', 'email', 'max:160'],
            'email_rate_limit_per_minute' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'email_queue_enabled' => ['required', 'boolean'],
        ];
    }
}
