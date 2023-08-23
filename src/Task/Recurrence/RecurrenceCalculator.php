<?php

declare(strict_types=1);

namespace App\Task\Recurrence;

use App\Task\Entity\Task;
use App\Task\Provider\TaskRecurrenceProvider;

readonly class RecurrenceCalculator
{
    public function __construct(
        private TaskRecurrenceProvider $recurrenceProvider
    ) {
    }

    public function getRecurrence(Task $task): void
    {
        $recurrence = $this->recurrenceProvider->getCurrentTaskRecurrence($task);

        $x = 1;
    }
}
