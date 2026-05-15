<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Audit\WriteAuditLogAction;
use App\Actions\Settings\UpdateSystemSettingAction;
use App\Actions\SystemSettings\UpdateSystemSettingsGroupAction;
use App\Actions\SystemSettings\UploadSystemAssetAction;
use App\Enums\AuditEventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TestNotificationChannelRequest;
use App\Http\Requests\Settings\UpdateAppearanceSettingsRequest;
use App\Http\Requests\Settings\UpdateIdCardSettingsRequest;
use App\Http\Requests\Settings\UpdateEmailSettingsRequest;
use App\Http\Requests\Settings\UpdateGeneralSettingsRequest;
use App\Http\Requests\Settings\UpdateLocalizationSettingsRequest;
use App\Http\Requests\Settings\UpdateNotificationSettingsRequest;
use App\Http\Requests\Settings\UpdateSecuritySettingsRequest;
use App\Http\Requests\Settings\UpdateSmsSettingsRequest;
use App\Http\Requests\Settings\UpdateTelegramSettingsRequest;
use App\Models\SystemSetting;
use App\Services\SystemSettings\SystemSettingsRegistry;
use App\Services\SystemSettings\SystemSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SystemSettingController extends Controller
{
    public function __construct(
        private readonly SystemSettingsService $settingsService,
        private readonly UpdateSystemSettingsGroupAction $updateGroupAction,
        private readonly UploadSystemAssetAction $uploadSystemAssetAction,
        private readonly WriteAuditLogAction $writeAuditLogAction,
    ) {}

    public function index(): Response
    {
        $this->authorize('view', SystemSetting::class);

        $user = Auth::user();

        $groups = [];
        foreach (SystemSettingsRegistry::groups() as $group) {
            $groups[$group] = [
                'fields' => $this->settingsService->getGroupForAdmin($group),
                'can_manage' => $user?->can('system-settings.manage'.ucfirst($group)) ?? false,
            ];
        }

        return Inertia::render('SystemSettings/Index', [
            'settingGroups' => $groups,
            'can' => [
                'view' => $user?->can('system-settings.view') ?? false,
                'update' => $user?->can('system-settings.update') ?? false,
                'manageGeneral' => $user?->can('system-settings.manageGeneral') ?? false,
                'manageLocalization' => $user?->can('system-settings.manageLocalization') ?? false,
                'manageNotifications' => $user?->can('system-settings.manageNotifications') ?? false,
                'manageEmail' => $user?->can('system-settings.manageEmail') ?? false,
                'manageSms' => $user?->can('system-settings.manageSms') ?? false,
                'manageTelegram' => $user?->can('system-settings.manageTelegram') ?? false,
                'manageSecurity' => $user?->can('system-settings.manageSecurity') ?? false,
                'manageAppearance' => $user?->can('system-settings.manageAppearance') ?? false,
                'manageIdCards' => $user?->can('system-settings.manageIdCards') ?? false,
                'clearCache' => $user?->can('system-settings.clearCache') ?? false,
                'testChannels' => $user?->can('system-settings.testNotificationChannels') ?? false,
            ],
        ]);
    }

    public function updateGeneral(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $assets = [
            'identity_system_logo' => $request->file('identity_system_logo'),
            'favicon' => $request->file('favicon'),
        ];

        unset($validated['identity_system_logo'], $validated['favicon']);

        $this->updateGroupAction->execute(
            SystemSettingsRegistry::GROUP_GENERAL,
            $validated,
            $request->user(),
        );

        foreach ($assets as $key => $file) {
            if ($file !== null) {
                $this->uploadSystemAssetAction->execute($key, $file, $request->user());
            }
        }

        return back()->with('flash', ['message' => __('settings.messages.general_updated'), 'type' => 'success']);
    }

    public function updateLocalization(UpdateLocalizationSettingsRequest $request): RedirectResponse
    {
        $this->updateGroupAction->execute(
            SystemSettingsRegistry::GROUP_LOCALIZATION,
            $request->validated(),
            $request->user(),
        );

        return back()->with('flash', ['message' => __('settings.messages.localization_updated'), 'type' => 'success']);
    }

    public function updateNotifications(UpdateNotificationSettingsRequest $request): RedirectResponse
    {
        $this->updateGroupAction->execute(
            SystemSettingsRegistry::GROUP_NOTIFICATIONS,
            $request->validated(),
            $request->user(),
        );

        return back()->with('flash', ['message' => __('settings.messages.notifications_updated'), 'type' => 'success']);
    }

    public function updateEmail(UpdateEmailSettingsRequest $request): RedirectResponse
    {
        $this->updateGroupAction->execute(
            SystemSettingsRegistry::GROUP_EMAIL,
            $request->validated(),
            $request->user(),
        );

        return back()->with('flash', ['message' => __('settings.messages.email_updated'), 'type' => 'success']);
    }

    public function updateSms(UpdateSmsSettingsRequest $request): RedirectResponse
    {
        $this->updateGroupAction->execute(
            SystemSettingsRegistry::GROUP_SMS,
            $request->validated(),
            $request->user(),
        );

        return back()->with('flash', ['message' => __('settings.messages.sms_updated'), 'type' => 'success']);
    }

    public function updateTelegram(UpdateTelegramSettingsRequest $request): RedirectResponse
    {
        $this->updateGroupAction->execute(
            SystemSettingsRegistry::GROUP_TELEGRAM,
            $request->validated(),
            $request->user(),
        );

        return back()->with('flash', ['message' => __('settings.messages.telegram_updated'), 'type' => 'success']);
    }

    public function updateSecurity(UpdateSecuritySettingsRequest $request): RedirectResponse
    {
        $this->updateGroupAction->execute(
            SystemSettingsRegistry::GROUP_SECURITY,
            $request->validated(),
            $request->user(),
        );

        return back()->with('flash', ['message' => __('settings.messages.security_updated'), 'type' => 'success']);
    }

    public function updateAppearance(UpdateAppearanceSettingsRequest $request): RedirectResponse
    {
        $this->updateGroupAction->execute(
            SystemSettingsRegistry::GROUP_APPEARANCE,
            $request->validated(),
            $request->user(),
        );

        return back()->with('flash', ['message' => __('settings.messages.appearance_updated'), 'type' => 'success']);
    }

    public function updateIdCards(UpdateIdCardSettingsRequest $request): RedirectResponse
    {
        $this->updateGroupAction->execute(
            SystemSettingsRegistry::GROUP_ID_CARDS,
            $request->validated(),
            $request->user(),
        );

        return back()->with('flash', ['message' => __('settings.messages.id_cards_updated'), 'type' => 'success']);
    }

    public function clearCache(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('system-settings.clearCache'), 403);
        $this->settingsService->clearCache();

        $this->writeAuditLogAction->execute(
            AuditEventType::SettingsCacheCleared,
            $request->user(),
            null,
            null,
            newValues: ['scope' => 'system_settings'],
        );

        return back()->with('flash', ['message' => __('settings.messages.cache_cleared'), 'type' => 'success']);
    }

    public function testEmail(TestNotificationChannelRequest $request): RedirectResponse
    {
        $recipient = $request->validated('recipient') ?: $this->settingsService->get('email', 'email_test_recipient');

        return $this->handleTestChannelResult(
            $request,
            channel: 'email',
            target: is_string($recipient) ? $recipient : null,
        );
    }

    public function testSms(TestNotificationChannelRequest $request): RedirectResponse
    {
        $phone = $request->validated('phone') ?: $this->settingsService->get('sms', 'sms_test_phone');

        return $this->handleTestChannelResult(
            $request,
            channel: 'sms',
            target: is_string($phone) ? $phone : null,
        );
    }

    public function testTelegram(TestNotificationChannelRequest $request): RedirectResponse
    {
        $chatId = $request->validated('chat_id') ?: $this->settingsService->get('telegram', 'telegram_test_chat_id');

        return $this->handleTestChannelResult(
            $request,
            channel: 'telegram',
            target: is_string($chatId) ? $chatId : null,
        );
    }

    /**
     * Legacy per-row update preserved for backward compat.
     */
    public function update(
        Request $request,
        SystemSetting $setting,
        UpdateSystemSettingAction $action,
    ): RedirectResponse {
        $this->authorize('update', $setting);

        $request->validate(['value' => ['nullable', 'string', 'max:2000']]);

        $action->execute($setting, (string) $request->input('value', ''), $request->user());

        $this->settingsService->clearCache();

        return back()->with('flash', ['message' => __('settings.messages.setting_updated'), 'type' => 'success']);
    }

    private function handleTestChannelResult(
        Request $request,
        string $channel,
        ?string $target,
    ): RedirectResponse {
        $configured = $target !== null && $target !== '';

        $this->writeAuditLogAction->execute(
            AuditEventType::NotificationChannelTested,
            $request->user(),
            null,
            null,
            newValues: [
                'channel' => $channel,
                'configured' => $configured,
                'target' => $configured ? 'configured' : 'not_configured',
            ],
            reason: 'system_settings_test_channel',
        );

        if (! $configured) {
            return back()->with('flash', [
                'message' => __('settings.messages.test_channel_missing', ['channel' => ucfirst($channel)]),
                'type' => 'warning',
            ]);
        }

        return back()->with('flash', [
            'message' => __('settings.messages.test_channel_queued', ['channel' => ucfirst($channel)]),
            'type' => 'success',
        ]);
    }
}
