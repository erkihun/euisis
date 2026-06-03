<?php

declare(strict_types=1);

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class TransportSettingsController extends Controller
{
    /** @var array<string, bool> */
    private const DEFAULTS = [
        'require_pass_for_scan' => true,
        'allow_pay_as_you_go' => false,
        'scan_nonce_required' => true,
    ];

    public function index(): Response
    {
        return Inertia::render('Transport/Settings/Index', [
            'settings' => $this->settings(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'require_pass_for_scan' => ['required', 'boolean'],
            'allow_pay_as_you_go' => ['required', 'boolean'],
            'scan_nonce_required' => ['required', 'boolean'],
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::query()->updateOrCreate(
                ['group' => 'transport', 'key' => $key],
                [
                    'value' => $value ? '1' : '0',
                    'type' => 'boolean',
                    'label_en' => str($key)->replace('_', ' ')->headline()->toString(),
                    'label_am' => str($key)->replace('_', ' ')->headline()->toString(),
                    'default_value' => self::DEFAULTS[$key] ? '1' : '0',
                    'is_public' => false,
                    'is_encrypted' => false,
                    'is_system' => false,
                    'is_required' => true,
                    'updated_by' => $request->user()?->id,
                ],
            );
        }

        Cache::forget('system_settings_all');
        Cache::forget('system_settings_public');

        return back()->with('flash', ['type' => 'success', 'message' => __('transport.settings_updated')]);
    }

    /** @return array<string, bool> */
    private function settings(): array
    {
        $rows = SystemSetting::query()
            ->where('group', 'transport')
            ->whereIn('key', array_keys(self::DEFAULTS))
            ->get()
            ->keyBy('key');

        return collect(self::DEFAULTS)
            ->map(fn (bool $default, string $key): bool => $rows->get($key)?->typedValue() ?? $default)
            ->all();
    }
}
