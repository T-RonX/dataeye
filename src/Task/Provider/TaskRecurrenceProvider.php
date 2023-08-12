<?php

declare(strict_types=1);

namespace App\Task\Provider;

use App\DateTimeProvider\DateTimeProvider;
use App\Task\Entity\Task;
use App\Task\Entity\TaskRecurrence;

readonly class TaskRecurrenceProvider
{
    public function __construct(
        private DateTimeProvider $dateTimeProvider,
    ) {
    }

    public function getCurrentTaskRecurrence(Task $task): ?TaskRecurrence
    {
        foreach ($task->getRecurrences() as $recurrence)
        {

        }

        return null;
    }
}
