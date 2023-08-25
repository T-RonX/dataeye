<?php

declare(strict_types=1);

namespace App\Task\Recurrence;

use App\Task\Entity\TaskRecurrence;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.task.recurrence.type')]

interface TypeHandlerInterface
{
    /**
     * @return DateTimeInterface[]
     */
    public function getRecurringDates(CarbonInterface $startsAt, DateTimeInterface $endsAt, TaskRecurrence $recurrence): array;
}
