<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('national_id')) {
            $this->merge(['national_id' => trim((string) $this->input('national_id', '')) ?: null]);
        }
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'status'                => ['in:active,inactive'],
            'roles'                 => ['array'],
            'roles.*'               => ['string', 'exists:roles,name'],
            'profile_photo'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'national_id'           => ['nullable', 'string', 'max:100', 'unique:users,national_id'],
            'phone_number'          => ['nullable', 'string', 'max:30', 'regex:/^[+\d\s\-()]+$/'],
            'gender'                => ['nullable', 'string', 'in:male,female,other,not_specified'],
        ];
    }
}
