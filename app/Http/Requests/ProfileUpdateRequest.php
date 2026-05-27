<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $nid = filled($this->input('national_id')) ? trim((string) $this->input('national_id')) : null;
        $this->merge([
            'national_id' => $nid,
            'national_id_hash' => $nid !== null ? hash('sha256', $nid) : null,
            'phone_number' => filled($this->input('phone_number')) ? trim((string) $this->input('phone_number')) : null,
            'gender' => filled($this->input('gender')) ? $this->input('gender') : null,
        ]);
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($userId),
            ],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'national_id' => ['nullable', 'string', 'max:100'],
            'national_id_hash' => [
                'nullable', 'string', 'size:64',
                Rule::unique('users', 'national_id_hash')->ignore($userId),
            ],
            'phone_number' => ['nullable', 'string', 'max:30', 'regex:/^[+\d\s\-()]+$/'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other', 'not_specified'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'profile_photo' => __('profile.profile_photo'),
            'national_id' => __('profile.national_id'),
            'phone_number' => __('profile.phone_number'),
            'gender' => __('profile.gender'),
        ];
    }

    public function messages(): array
    {
        return [
            'profile_photo.image' => __('profile.photo_must_be_image'),
            'profile_photo.max' => __('profile.photo_too_large'),
        ];
    }
}
