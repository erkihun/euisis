<?php

declare(strict_types=1);

namespace App\Services\Cafeteria;

use App\Models\CafeteriaDayRule;
use App\Models\CafeteriaSpecialDay;
use App\Models\PublicHoliday;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WorkingDayCalendarService
{
    /** @var Collection<int, string>|null */
    private ?Collection $holidayCache = null;

    /** @var Collection<int, CafeteriaDayRule>|null */
    private ?Collection $dayRuleCache = null;

    public function isHoliday(Carbon $date): bool
    {
        return $this->getHolidayDates()->contains($date->toDateString());
    }

    public function isWeekend(Carbon $date): bool
    {
        return $date->isWeekend();
    }

    /**
     * Full working-day check using priority order:
     * 1. Special day override (highest)
     * 2. Public holiday
     * 3. Weekly day rule
     * 4. Default weekend behaviour
     */
    public function isWorkingDay(Carbon $date, bool $excludeWeekends = true): bool
    {
        // 1. Special day override
        $special = $this->getSpecialDayForDate($date);
        if ($special !== null) {
            return $special->is_open && $special->is_subsidy_day;
        }

        // 2. Public holiday
        if ($this->isHoliday($date)) {
            return false;
        }

        // 3. Weekly day rule
        $rule = $this->getDayRule($date);
        if ($rule !== null) {
            return $rule->is_open && $rule->is_subsidy_day;
        }

        // 4. Default
        if ($excludeWeekends && $this->isWeekend($date)) {
            return false;
        }

        return true;
    }

    /**
     * Returns true if cafeteria is open (may have no subsidy).
     */
    public function isCafeteriaOpen(Carbon $date): bool
    {
        $special = $this->getSpecialDayForDate($date);
        if ($special !== null) {
            return $special->is_open;
        }

        $rule = $this->getDayRule($date);
        if ($rule !== null) {
            return $rule->is_open;
        }

        return ! $this->isWeekend($date);
    }

    /**
     * Returns true when $date qualifies for subsidy allocation.
     */
    public function isSubsidyDay(Carbon $date): bool
    {
        $special = $this->getSpecialDayForDate($date);
        if ($special !== null) {
            return $special->is_open && $special->is_subsidy_day;
        }

        if ($this->isHoliday($date)) {
            return false;
        }

        $rule = $this->getDayRule($date);
        if ($rule !== null) {
            return $rule->is_open && $rule->is_subsidy_day;
        }

        return ! $this->isWeekend($date);
    }

    public function nextWorkingDay(Carbon $date, bool $excludeWeekends = true): Carbon
    {
        $next = $date->copy()->addDay();

        while (! $this->isWorkingDay($next, $excludeWeekends)) {
            $next->addDay();
        }

        return $next;
    }

    public function workingDaysBetween(Carbon $start, Carbon $end, bool $excludeWeekends = true): int
    {
        $count = 0;
        $current = $start->copy()->startOfDay();
        $endDay  = $end->copy()->startOfDay();

        while ($current->lte($endDay)) {
            if ($this->isWorkingDay($current, $excludeWeekends)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    public function holidaysBetween(Carbon $start, Carbon $end): Collection
    {
        return PublicHoliday::query()
            ->where('is_active', true)
            ->whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('holiday_date')
            ->get();
    }

    public function getHolidayForDate(Carbon $date): ?PublicHoliday
    {
        return PublicHoliday::query()
            ->where('is_active', true)
            ->where('holiday_date', $date->toDateString())
            ->first();
    }

    public function getSpecialDayForDate(Carbon $date): ?CafeteriaSpecialDay
    {
        return CafeteriaSpecialDay::query()
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->where('special_date', $date->toDateString())
            ->first();
    }

    public function getDayRule(Carbon $date): ?CafeteriaDayRule
    {
        $isoDay = (int) $date->isoFormat('E'); // 1=Mon … 7=Sun

        return $this->getDayRules()->first(fn (CafeteriaDayRule $r) => $r->day_of_week === $isoDay);
    }

    public function clearCache(): void
    {
        $this->holidayCache = null;
        $this->dayRuleCache = null;
    }

    private function getHolidayDates(): Collection
    {
        if ($this->holidayCache === null) {
            $this->holidayCache = PublicHoliday::query()
                ->where('is_active', true)
                ->pluck('holiday_date')
                ->map(fn ($d) => Carbon::parse($d)->toDateString());
        }

        return $this->holidayCache;
    }

    private function getDayRules(): Collection
    {
        if ($this->dayRuleCache === null) {
            $this->dayRuleCache = CafeteriaDayRule::query()
                ->where('is_active', true)
                ->get();
        }

        return $this->dayRuleCache;
    }
}
