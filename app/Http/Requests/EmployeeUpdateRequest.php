<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EmployeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class EmployeeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employees.manage') ?? false;
    }

    public function rules(): array
    {
        $employeeId = (string) $this->route('employee')?->getKey();

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255', "unique:employees,email,{$employeeId},id"],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:32'],
            'status' => ['required', new Enum(EmployeeStatus::class)],
            'national_id' => ['nullable', 'string', 'size:16', 'regex:/^[0-9]{16}$/', "unique:employees,national_id,{$employeeId},id"],
            'photo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }
}
