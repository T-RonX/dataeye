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
     * @return CarbonInterface[]
     */
    public function getRecurringDates(CarbonInterface $startDate, TaskRecurrence $recurrence, int|DateTimeInterface $limit): array;
}
