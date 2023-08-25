<?php

declare(strict_types=1);

namespace App\Task\Recurrence\TypeHandler;

use App\Task\Entity\TaskRecurrence;
use App\Task\Entity\TaskRecurrenceMonthAbsolute;
use App\Task\Entity\TaskRecurrenceMonthRelative;
use App\Task\Enum\Day;
use App\Task\Enum\RecurrenceType;
use App\Task\Enum\WeekOrdinal;
use App\Task\Recurrence\TypeHandlerInterface;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(RecurrenceType::Month->name)]
class MonthTypeHandler extends BaseTypeHandler implements TypeHandlerInterface
{
    public function getRecurringDates(CarbonInterface $startsAt, DateTimeInterface $endsAt, TaskRecurrence|TaskRecurrenceMonthAbsolute|TaskRecurrenceMonthRelative $recurrence): array
    {
        $interval = $recurrence->getInterval();
        $upcomingDates = [];
        $monthsToAdd = 0;
        $remaining = 100;
        $doCollectDates = false;
        $startsAt = $startsAt->setTime(0, 0);
        $startDate = $startsAt->startOfMonth();
        $getDay = $recurrence instanceof TaskRecurrenceMonthAbsolute ? $this->getAbsoluteDate(...) : $this->getRelativeDate(...);

        while ($remaining)
        {
            $nextDate = $getDay($recurrence, $startDate->addMonths($monthsToAdd));

            if ($nextDate && ($doCollectDates || $nextDate >= $startsAt))
            {
                $doCollectDates = true;
                $upcomingDates[] = $nextDate;
                --$remaining;
            }

            $monthsToAdd += $interval;
        }

        return $upcomingDates;
    }

    private function getAbsoluteDate(TaskRecurrenceMonthAbsolute $recurrence, CarbonInterface $month): ?DateTimeInterface
    {
        $daysInMonth = $month->daysInMonth;
        $dayNumber = $recurrence->getDayNumber();

        return $dayNumber <= $daysInMonth ? $month->subDay()->addDays($dayNumber) : null;
    }

    private function getRelativeDate(TaskRecurrenceMonthRelative $recurrence, CarbonInterface $month): DateTimeInterface
    {
        return match ($recurrence->getWeekOrdinal())  {
            WeekOrdinal::First => $this->getFirstOccurrence($month, $recurrence->getDay()),
            WeekOrdinal::Second => $this->getSecondOccurrence($month, $recurrence->getDay()),
            WeekOrdinal::Third => $this->getThirdOccurrence($month, $recurrence->getDay()),
            WeekOrdinal::Fourth => $this->getFourthOccurrence($month, $recurrence->getDay()),
            WeekOrdinal::Last => $this->getLastOccurrence($month, $recurrence->getDay()),
        };
    }

    private function getFirstOccurrence(CarbonInterface $month, Day $day): DateTimeInterface
    {
        return $month->subDay()->next($day->toCarbon());
    }

    private function getSecondOccurrence(CarbonInterface $month, Day $day): DateTimeInterface
    {
        return $month->subDay()->next($day->toCarbon())->addWeek();
    }

    private function getThirdOccurrence(CarbonInterface $month, Day $day): DateTimeInterface
    {
        return $month->subDay()->next($day->toCarbon())->addWeeks(2);
    }

    private function getFourthOccurrence(CarbonInterface $month, Day $day): DateTimeInterface
    {
        return $month->subDay()->next($day->toCarbon())->addWeeks(3);
    }

    private function getLastOccurrence(CarbonInterface $month, Day $day): DateTimeInterface
    {
        $lastDayOfMonth = $month->endOfMonth();
        $daysUntilTargetDay = ($lastDayOfMonth->dayOfWeek - $day->toCarbon() + 7) % 7;

        return $lastDayOfMonth->subDays($daysUntilTargetDay);
    }
}
