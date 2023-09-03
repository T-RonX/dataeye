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
    public function getRecurringDates(CarbonInterface $startsAt, TaskRecurrence|TaskRecurrenceDay $recurrence, int|DateTimeInterface $limit): array
    {
        $upcomingDates = [];
        $count = 0;
        $now = $this->dateTimeProvider->getNow($startsAt->getTimezone());
        $nextDateTime = $startsAt;

        while (!$this->isLimitReached($limit, $nextDateTime, $count))
        {
            if ($nextDateTime >= $now)
            {
                $upcomingDates[] = $nextDateTime;
                ++$count;
            }

            $nextDateTime = $nextDateTime->addDays(1);
        }

        return $upcomingDates;
    }
}
