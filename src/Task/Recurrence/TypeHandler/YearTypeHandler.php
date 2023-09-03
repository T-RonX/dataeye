<?php

declare(strict_types=1);

namespace App\Task\Recurrence\TypeHandler;

use App\Task\Entity\TaskRecurrence;
use App\Task\Entity\TaskRecurrenceYearAbsolute;
use App\Task\Entity\TaskRecurrenceYearRelative;
use App\Task\Enum\Day;
use App\Task\Enum\DayOrdinal;
use App\Task\Enum\Month;
use App\Task\Enum\RecurrenceType;
use App\Task\Recurrence\Exception\InvalidRecurrenceException;
use App\Task\Recurrence\TypeHandlerInterface;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(RecurrenceType::Year->name)]
class YearTypeHandler extends BaseTypeHandler implements TypeHandlerInterface
{
    private const LEAP_YEAR_MAX_INTERVAL = 9;

    public function getRecurringDates(CarbonInterface $startsAt, TaskRecurrence|TaskRecurrenceYearAbsolute|TaskRecurrenceYearRelative $recurrence, int|DateTimeInterface $limit): array
    {
        $upcomingDates = [];
        $yearsToAdd = 0;
        $doCollectDates = false;
        $startDate = $startsAt->startOfMonth()->setTimeFrom($startsAt);;
        $getDay = $recurrence instanceof TaskRecurrenceYearAbsolute ? $this->getAbsoluteDate(...) : $this->getRelativeDate(...);
        $invalidAttemptsRemaining = self::LEAP_YEAR_MAX_INTERVAL - 1;
        $now = $this->dateTimeProvider->getNow($startsAt->getTimezone());
        $count = 0;

        while ($invalidAttemptsRemaining)
        {
            try
            {
                $nextDateTime = $getDay($recurrence, $startDate->addYears($yearsToAdd));
                $invalidAttemptsRemaining = self::LEAP_YEAR_MAX_INTERVAL - 1;

                if ($this->isLimitReached($limit, $nextDateTime, $count))
                {
                    break;
                }
            }
            catch (InvalidRecurrenceException)
            {
                ++$yearsToAdd;
                --$invalidAttemptsRemaining;
                continue;
            }

            if ($nextDateTime && ($doCollectDates || $nextDateTime >= $now))
            {
                $doCollectDates = true;
                $upcomingDates[] = $nextDateTime;
                ++$count;
            }

            ++$yearsToAdd;
        }

        return $upcomingDates;
    }

    private function getAbsoluteDate(TaskRecurrenceYearAbsolute $recurrence, CarbonInterface $year): ?DateTimeInterface
    {
        $month = $year->setMonth($recurrence->getMonth()->toCarbon())->firstOfMonth()->setTimeFrom($year);
        $daysInMonth = $month->daysInMonth;
        $dayNumber = $recurrence->getDayNumber();

        if ($dayNumber > $daysInMonth)
        {
            throw new InvalidRecurrenceException();
        }

        return $month->subDay()->addDays($dayNumber);
    }

    private function getRelativeDate(TaskRecurrenceYearRelative $recurrence, CarbonInterface $year): DateTimeInterface
    {
        return match ($recurrence->getDayOrdinal())  {
            DayOrdinal::First => $this->getFirstOccurrence($year, $recurrence->getMonth(), $recurrence->getDay()),
            DayOrdinal::Second => $this->getSecondOccurrence($year, $recurrence->getMonth(),$recurrence->getDay()),
            DayOrdinal::Third => $this->getThirdOccurrence($year, $recurrence->getMonth(),$recurrence->getDay()),
            DayOrdinal::Fourth => $this->getFourthOccurrence($year, $recurrence->getMonth(),$recurrence->getDay()),
            DayOrdinal::Last => $this->getLastOccurrence($year, $recurrence->getMonth(),$recurrence->getDay()),
        };
    }

    private function getFirstOccurrence(CarbonInterface $year, Month $month, Day $day): DateTimeInterface
    {
        return $year->setMonth($month->toCarbon())->firstOfMonth()->subDay()->next($day->toCarbon())->setTimeFrom($year);
    }

    private function getSecondOccurrence(CarbonInterface $year, Month $month, Day $day): DateTimeInterface
    {
        return $year->setMonth($month->toCarbon())->firstOfMonth()->subDay()->next($day->toCarbon())->addWeek()->setTimeFrom($year);
    }

    private function getThirdOccurrence(CarbonInterface $year, Month $month, Day $day): DateTimeInterface
    {
        return $year->setMonth($month->toCarbon())->firstOfMonth()->subDay()->next($day->toCarbon())->addWeeks(2)->setTimeFrom($year);
    }

    private function getFourthOccurrence(CarbonInterface $year, Month $month, Day $day): DateTimeInterface
    {
        return $year->setMonth($month->toCarbon())->firstOfMonth()->subDay()->next($day->toCarbon())->addWeeks(3)->setTimeFrom($year);
    }

    private function getLastOccurrence(CarbonInterface $year, Month $month, Day $day): DateTimeInterface
    {
        $lastDayOfMonth = $year->setMonth($month->toCarbon())->endOfMonth()->setTimeFrom($year);
        $daysUntilTargetDay = ($lastDayOfMonth->dayOfWeek - $day->toCarbon() + 7) % 7;

        return $lastDayOfMonth->subDays($daysUntilTargetDay);
    }
}
