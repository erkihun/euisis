<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EmployeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class EmployeeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employees.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'employee_number' => ['nullable', 'string', 'max:255', 'unique:employees,employee_number'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'size:16', 'regex:/^[0-9]{16}$/', 'unique:employees,national_id'],
            'photo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:32'],
            'status' => ['required', new Enum(EmployeeStatus::class)],
            'organization_id' => ['required', 'uuid', 'exists:organizations,id'],
            'hierarchy_version_id' => ['nullable', 'uuid', 'exists:hierarchy_versions,id'],
            'position_id' => ['nullable', 'uuid', 'exists:positions,id'],
            'position_title' => ['nullable', 'string', 'max:255'],
            'effective_from' => ['required', 'date'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
