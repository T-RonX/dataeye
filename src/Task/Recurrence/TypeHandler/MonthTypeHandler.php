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
    public function getRecurringDates(CarbonInterface $startsAt, TaskRecurrence|TaskRecurrenceMonthAbsolute|TaskRecurrenceMonthRelative $recurrence, int|DateTimeInterface $limit): array
    {
        $interval = $recurrence->getInterval();
        $upcomingDates = [];
        $monthsToAdd = 0;
        $doCollectDates = false;
        $startDate = $startsAt->startOfMonth()->setTimeFrom($startsAt);
        $getDay = $recurrence instanceof TaskRecurrenceMonthAbsolute ? $this->getAbsoluteDate(...) : $this->getRelativeDate(...);
        $now = $this->dateTimeProvider->getNow($startsAt->getTimezone());
        $count = 0;

        while (true)
        {
                $nextDateTime = $getDay($recurrence, $startDate->addMonths($monthsToAdd));

            if ($this->isLimitReached($limit, $nextDateTime, $count))
            {
                break;
            }

            if ($nextDateTime && ($doCollectDates || $nextDateTime >= $now))
            {
                $doCollectDates = true;
                $upcomingDates[] = $nextDateTime;
                ++$count;
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
        return $month->subDay()->next($day->toCarbon())->setTimeFrom($month);
    }

    private function getSecondOccurrence(CarbonInterface $month, Day $day): DateTimeInterface
    {
        return $month->subDay()->next($day->toCarbon())->addWeek()->setTimeFrom($month);
    }

    private function getThirdOccurrence(CarbonInterface $month, Day $day): DateTimeInterface
    {
        return $month->subDay()->next($day->toCarbon())->addWeeks(2)->setTimeFrom($month);
    }

    private function getFourthOccurrence(CarbonInterface $month, Day $day): DateTimeInterface
    {
        return $month->subDay()->next($day->toCarbon())->addWeeks(3)->setTimeFrom($month);
    }

    private function getLastOccurrence(CarbonInterface $month, Day $day): DateTimeInterface
    {
        $lastDayOfMonth = $month->endOfMonth()->setTimeFrom($month);
        $daysUntilTargetDay = ($lastDayOfMonth->dayOfWeek - $day->toCarbon() + 7) % 7;

        return $lastDayOfMonth->subDays($daysUntilTargetDay);
    }
}
