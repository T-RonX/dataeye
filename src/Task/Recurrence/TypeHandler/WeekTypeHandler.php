<?php

declare(strict_types=1);

namespace App\Task\Recurrence\TypeHandler;

use App\Task\Entity\TaskRecurrence;
use App\Task\Entity\TaskRecurrenceWeek;
use App\Task\Enum\RecurrenceType;
use App\Task\Recurrence\TypeHandlerInterface;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(RecurrenceType::Week->name)]
class WeekTypeHandler extends BaseTypeHandler implements TypeHandlerInterface
{
    public function getRecurringDates(CarbonInterface $startsAt, DateTimeInterface $endsAt, TaskRecurrence|TaskRecurrenceWeek $recurrence): array
    {
        $this->validateType($recurrence, TaskRecurrenceWeek::class);

        $daysToRecur = $recurrence->getDays();

        if (!$daysToRecur)
        {
            return [];
        }

        $startsAt = $startsAt->setTime(0, 0);
        $startsDate = $startsAt->startOfWeek($this->getFirstDayOfWeek()->toCarbon())->subDay();
        $currentDate = $startsDate;

        $upcomingDates = [];

        $interval = $recurrence->getInterval();
        $numberOfDates = 100;
        $weeksToAdd = 0;
        $doCollectDates = false;

        $days = $this->getWeekDaysOrdered($recurrence->getDays());

        while (count($upcomingDates) < $numberOfDates)
        {
            foreach ($days as $day)
            {
                $nextDate = $currentDate->next($day->toCarbon());

                if ($doCollectDates || $nextDate >= $startsAt)
                {
                    $doCollectDates = true;
                    $upcomingDates[] = $nextDate;
                }

                if (count($upcomingDates) >= $numberOfDates)
                {
                    break;
                }
            }

            $weeksToAdd += $interval;
            $currentDate = $startsDate->addWeeks($weeksToAdd);
        }

        sort($upcomingDates);

        return $upcomingDates;
    }
}
