<?php

declare(strict_types=1);

namespace App\Http\Requests\InstitutionOffices;

use App\Enums\InstitutionOfficeLevel;
use App\Enums\InstitutionOfficeStatus;
use App\Models\InstitutionOffice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class StoreInstitutionOfficeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('institution-offices.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'geographic_organization_id' => $this->input('geographic_organization_id') ?: null,
            'parent_office_id' => $this->input('parent_office_id') ?: null,
            'name_am' => $this->input('name_am') ?: null,
            'short_name_en' => $this->input('short_name_en') ?: null,
            'short_name_am' => $this->input('short_name_am') ?: null,
            'opened_on' => $this->input('opened_on') ?: null,
            'closed_on' => $this->input('closed_on') ?: null,
            'address_en' => $this->input('address_en') ?: null,
            'address_am' => $this->input('address_am') ?: null,
            'phone_number' => $this->input('phone_number') ?: null,
            'email' => $this->input('email') ?: null,
            'notes' => $this->input('notes') ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'institution_id' => ['required', 'uuid', 'exists:organizations,id'],
            'geographic_organization_id' => ['nullable', 'uuid', 'exists:organizations,id'],
            'parent_office_id' => ['nullable', 'uuid', 'exists:institution_offices,id'],
            'office_level' => ['required', new Enum(InstitutionOfficeLevel::class)],
            'office_code' => ['required', 'string', 'max:50', 'unique:institution_offices,office_code'],
            'name_en' => ['required', 'string', 'max:255'],
            'name_am' => ['nullable', 'string', 'max:255'],
            'short_name_en' => ['nullable', 'string', 'max:100'],
            'short_name_am' => ['nullable', 'string', 'max:100'],
            'assigned_scope_type' => ['sometimes', 'string', 'in:self,subtree'],
            'is_head_office' => ['sometimes', 'boolean'],
            'status' => ['sometimes', new Enum(InstitutionOfficeStatus::class)],
            'opened_on' => ['nullable', 'date'],
            'closed_on' => ['nullable', 'date', 'after_or_equal:opened_on'],
            'address_en' => ['nullable', 'string', 'max:500'],
            'address_am' => ['nullable', 'string', 'max:500'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $parentOfficeId = $this->string('parent_office_id')->toString();
                $institutionId = $this->string('institution_id')->toString();

                if ($parentOfficeId === '' || $institutionId === '') {
                    return;
                }

                $parentOffice = InstitutionOffice::query()->find($parentOfficeId);

                if ($parentOffice !== null && $parentOffice->institution_id !== $institutionId) {
                    $validator->errors()->add(
                        'parent_office_id',
                        __('institution-offices.validation.parent_must_same_institution'),
                    );
                }
            },
        ];
    }
}
