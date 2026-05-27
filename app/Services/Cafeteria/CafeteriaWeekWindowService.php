<?php

declare(strict_types=1);

namespace App\Services\Cafeteria;

use Illuminate\Support\Carbon;

/**
 * Provides the Mon–Fri cafeteria week window and computes which working
 * days remain from a given scan date through the Friday of that week,
 * excluding public holidays.
 */
class CafeteriaWeekWindowService
{
    public function __construct(private readonly WorkingDayCalendarService $calendar) {}

    /** Monday of the week containing $date. */
    public function weekStart(Carbon $date): Carbon
    {
        return $date->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
    }

    /** Friday of the week containing $date. */
    public function weekEnd(Carbon $date): Carbon
    {
        return $date->copy()->startOfWeek(Carbon::MONDAY)->addDays(4)->startOfDay();
    }

    /**
     * Returns an ordered array of date strings ('Y-m-d') representing
     * working days from $from (inclusive) through Friday of the same week,
     * excluding weekends and public holidays.
     *
     * @return list<string>
     */
    public function remainingWorkingDaysFrom(Carbon $from): array
    {
        $friday  = $this->weekEnd($from);
        $current = $from->copy()->startOfDay();
        $days    = [];

        while ($current->lte($friday)) {
            if ($this->calendar->isSubsidyDay($current)) {
                $days[] = $current->toDateString();
            }
            $current->addDay();
        }

        return $days;
    }

    /** True when $date falls on Saturday or Sunday. */
    public function isWeekend(Carbon $date): bool
    {
        return $date->isWeekend();
    }

    /**
     * True when $date is a cafeteria working day (Mon–Fri, not a public holiday).
     */
    public function isCafeteriaWorkingDay(Carbon $date): bool
    {
        return $this->calendar->isWorkingDay($date);
    }
}
