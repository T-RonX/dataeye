<?php

declare(strict_types=1);

namespace App\Task\Recurrence\TypeHandler;

use App\Task\Entity\TaskRecurrence;
use App\Task\Entity\TaskRecurrenceDay;
use App\Task\Enum\RecurrenceType;
use App\Task\Recurrence\TypeHandlerInterface;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(RecurrenceType::Day->name)]
class DayTypeHandler extends BaseTypeHandler implements TypeHandlerInterface
{
    public function getRecurringDates(CarbonInterface $startsAt, DateTimeInterface $endsAt, TaskRecurrence|TaskRecurrenceDay $recurrence): array
    {
        $interval = $recurrence->getInterval();
        $upcomingDates = [];
        $daysToAdd = 0;
        $remaining = 100;
        $startsDate = $startsAt->setTime(0, 0);

        while ($remaining--)
        {
            $upcomingDates[] = $startsDate->addDays($daysToAdd);
            $daysToAdd += $interval;
        }

        return $upcomingDates;
    }
}
