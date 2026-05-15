<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Occupation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOccupationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Occupation|null $occupation */
        $occupation = $this->route('occupation');

        return $occupation !== null && ($this->user()?->can('update', $occupation) ?? false);
    }

    public function rules(): array
    {
        /** @var Occupation $occupation */
        $occupation = $this->route('occupation');

        return [
            'isco_code' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/', Rule::unique('occupations', 'isco_code')->ignore($occupation->id)],
            'isco_major_group_code' => ['nullable', 'string', 'max:1'],
            'isco_sub_major_group_code' => ['nullable', 'string', 'max:2'],
            'isco_minor_group_code' => ['nullable', 'string', 'max:3'],
            'isco_unit_group_code' => ['nullable', 'string', 'max:4'],
            'skill_specialization' => ['nullable', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255', 'required_without:name_am'],
            'name_am' => ['nullable', 'string', 'max:255', 'required_without:name_en'],
            'description_en' => ['nullable', 'string'],
            'description_am' => ['nullable', 'string'],
            'skill_level' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
