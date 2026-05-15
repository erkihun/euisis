<?php

declare(strict_types=1);

namespace App\Http\Requests\IdCards;

use Illuminate\Foundation\Http\FormRequest;

class CreatePrintBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('id-cards.createPrintBatch')
            || $this->user()?->can('cards.manage')
            ? true
            : false;
    }

    public function rules(): array
    {
        return [
            'card_ids' => ['required', 'array', 'min:1'],
            'card_ids.*' => ['required', 'uuid', 'exists:id_cards,id'],
        ];
    }
}
