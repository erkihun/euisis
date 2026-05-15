<?php

declare(strict_types=1);

namespace App\Http\Requests\UserOrganizationScopes;

use App\Models\UserOrganizationScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserOrganizationScopeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $scope = $this->route('scope');

        return $scope instanceof UserOrganizationScope
            ? ($this->user()?->can('update', $scope) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'organization_id' => [
                Rule::requiredIf(fn () => $this->input('scope_type') !== 'citywide'),
                'nullable',
                'uuid',
                'exists:organizations,id',
            ],
            'scope_type' => ['required', 'string', Rule::in(['self', 'subtree', 'citywide', 'service_provider'])],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['boolean'],
        ];
    }
}
