<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Cafeteria\UpdateCafeteriaSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCafeteriaSettingsRequest;
use App\Http\Resources\CafeteriaDayRuleResource;
use App\Http\Resources\CafeteriaSubsidyRuleResource;
use App\Http\Resources\PublicHolidayResource;
use App\Models\CafeteriaDayRule;
use App\Models\CafeteriaSetting;
use App\Models\CafeteriaSubsidyRule;
use App\Models\PublicHoliday;
use App\Services\Cafeteria\CafeteriaSettingsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CafeteriaSettingController extends Controller
{
    public function __construct(private readonly CafeteriaSettingsService $settingsService) {}

    public function index(): Response
    {
        $this->authorize('view', CafeteriaSetting::class);

        $validTabs = ['general', 'subsidy', 'days', 'scan', 'day-rules', 'holidays', 'subsidy-rules', 'reports'];
        $tab = in_array(request()->query('tab'), $validTabs, true)
            ? request()->query('tab')
            : 'general';

        $user = request()->user();

        $dayRules = CafeteriaDayRule::query()
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->get();

        $year = request()->integer('year') ?: now()->year;
        $holidays = PublicHoliday::query()
            ->whereYear('holiday_date', $year)
            ->orderBy('holiday_date')
            ->get();

        $subsidyRules = CafeteriaSubsidyRule::query()
            ->withoutTrashed()
            ->orderBy('effective_from', 'desc')
            ->get();

        return Inertia::render('Cafeteria/Settings/Index', [
            'settings'     => $this->settingsService->all(),
            'activeTab'    => $tab,
            'dayRules'     => CafeteriaDayRuleResource::collection($dayRules),
            'holidays'     => PublicHolidayResource::collection($holidays),
            'holidaysYear' => $year,
            'subsidyRules' => CafeteriaSubsidyRuleResource::collection($subsidyRules),
            'can'          => [
                'update'            => $user?->can('cafeteria_settings.update') ?? false,
                'updateDayRules'    => $user?->can('cafeteria_day_rules.update') ?? false,
                'createHoliday'     => $user?->can('cafeteria_holidays.create') ?? false,
                'createSubsidyRule' => $user?->can('cafeteria_subsidy_rules.create') ?? false,
            ],
        ]);
    }

    public function update(UpdateCafeteriaSettingsRequest $request, UpdateCafeteriaSettingsAction $action): RedirectResponse
    {
        $action->execute($request->validated(), $request->user(), $request);

        return back()->with('flash', ['message' => __('cafeteria.settingsUpdated'), 'type' => 'success']);
    }
}
