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
    public function getRecurringDates(CarbonInterface $startsAt, TaskRecurrence|TaskRecurrenceWeek $recurrence, int|DateTimeInterface $limit): array
    {
        $this->validateType($recurrence, TaskRecurrenceWeek::class);

        $daysToRecur = $recurrence->getDays();

        if (!$daysToRecur)
        {
            return [];
        }

        $now = $this->dateTimeProvider->getNow($startsAt->getTimezone());
        $startsDate = $startsAt->startOfWeek($this->getFirstDayOfWeek()->toCarbon())->subDay()->setTimeFrom($startsAt);
        $currentDate = $startsDate;

        $upcomingDates = [];
        $interval = $recurrence->getInterval();
        $numberOfDates = 100;
        $weeksToAdd = 0;
        $doCollectDates = false;
        $count = 0;
        $days = $this->getWeekDaysOrdered($recurrence->getDays());

        while (true)
        {
            foreach ($days as $day)
            {
                $nextDateTime = $currentDate->next($day->toCarbon())->setTimeFrom($startsDate);

                if ($this->isLimitReached($limit, $nextDateTime, $count))
                {
                    break 2;
                }

                if ($doCollectDates || $nextDateTime >= $now)
                {
                    $doCollectDates = true;
                    $upcomingDates[] = $nextDateTime;
                    ++$count;
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
