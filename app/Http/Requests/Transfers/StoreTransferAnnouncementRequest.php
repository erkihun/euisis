<?php

declare(strict_types=1);

namespace App\Http\Requests\Transfers;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('transfers.announcements.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'positions' => ['required', 'array', 'min:1'],
            'positions.*.organization_id' => ['required', 'uuid', 'exists:organizations,id'],
            'positions.*.position_id' => ['required', 'uuid', 'exists:positions,id'],
            'positions.*.grade_level' => ['nullable', 'string', 'max:50'],
            'positions.*.salary_min' => ['nullable', 'numeric', 'min:0'],
            'positions.*.salary_max' => ['nullable', 'numeric', 'gte:positions.*.salary_min'],
            'positions.*.vacancy_count' => ['required', 'integer', 'min:1', 'max:999'],
            'eligibility_rules' => ['nullable', 'array'],
            'eligibility_rules.*' => ['string', 'max:500'],
            'required_documents' => ['nullable', 'array'],
            'required_documents.*' => ['string', 'max:100'],
            'opening_date' => ['required', 'date'],
            'closing_date' => ['required', 'date', 'after:opening_date'],
        ];
    }
}
