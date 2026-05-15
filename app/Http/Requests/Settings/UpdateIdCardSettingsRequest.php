<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIdCardSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('system-settings.manageIdCards') ?? false;
    }

    public function rules(): array
    {
        $hex = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];

        return [
            'front_bg_from'          => $hex,
            'front_bg_to'            => $hex,
            'front_text_primary'     => $hex,
            'front_text_secondary'   => $hex,
            'front_name_font_size'   => ['required', Rule::in(['xs', 'sm', 'base', 'lg'])],
            'front_label_font_size'  => ['required', Rule::in(['xs', 'sm'])],
            'city_name_en'           => ['required', 'string', 'max:120'],
            'city_name_am'           => ['required', 'string', 'max:120'],
            'bureau_name_en'         => ['required', 'string', 'max:120'],
            'bureau_name_am'         => ['required', 'string', 'max:120'],
            'show_organization_logo' => ['required', 'boolean'],
            'back_bg_from'           => $hex,
            'back_bg_to'             => $hex,
            'back_text_color'        => $hex,
            'return_address_en'      => ['required', 'string', 'max:300'],
            'return_address_am'      => ['required', 'string', 'max:300'],
            'show_magnetic_stripe'   => ['required', 'boolean'],
            'qr_size'                => ['required', Rule::in(['80', '100', '120'])],
            'card_padding'           => ['required', Rule::in(['compact', 'normal', 'spacious'])],
        ];
    }
}
