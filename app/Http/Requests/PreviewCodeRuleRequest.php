<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CodeRuleEntityType;
use App\Enums\CodeRuleResetFrequency;
use App\Enums\CodeRuleScopeStrategy;
use App\Enums\CodeRuleScopeType;
use App\Models\CodeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewCodeRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('preview', CodeRule::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'entity_type' => ['required', Rule::enum(CodeRuleEntityType::class)],
            'scope_type' => ['nullable', Rule::enum(CodeRuleScopeType::class)],
            'scope_id' => ['nullable', 'string', 'max:255'],
            'prefix' => ['nullable', 'string', 'max:50'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'format' => ['required', 'string', 'max:255'],
            'separator' => ['required', 'string', 'max:10'],
            'sequence_length' => ['required', 'integer', 'min:1', 'max:12'],
            'next_number' => ['required', 'integer', 'min:1'],
            'reset_frequency' => ['required', Rule::enum(CodeRuleResetFrequency::class)],
            'year_format' => ['nullable', 'string', 'max:10'],
            // Context fields for token resolution
            'organization_id' => ['nullable', 'string', 'max:255'],
            'organization_type_id' => ['nullable', 'string', 'max:255'],
            'parent_organization_id' => ['nullable', 'string', 'max:255'],
            'organization_unit_id' => ['nullable', 'string', 'max:255'],
            'organization_unit_type_id' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:255'],
            'position_id' => ['nullable', 'string', 'max:255'],
            'service_type_id' => ['nullable', 'string', 'max:255'],
            'service_provider_id' => ['nullable', 'string', 'max:255'],
            'request_type' => ['nullable', 'string', 'max:100'],
            'workflow_code' => ['nullable', 'string', 'max:100'],
            'approval_step_code' => ['nullable', 'string', 'max:100'],
            'document_type_code' => ['nullable', 'string', 'max:100'],
            'custom' => ['nullable', 'string', 'max:100'],
            'custom_1' => ['nullable', 'string', 'max:100'],
            'custom_2' => ['nullable', 'string', 'max:100'],
            'custom_3' => ['nullable', 'string', 'max:100'],
            'sequence_scope_strategy' => ['nullable', Rule::enum(CodeRuleScopeStrategy::class)],
            'sequence_scope_tokens' => ['nullable', 'array'],
        ];
    }
}
