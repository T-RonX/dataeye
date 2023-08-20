<?php

declare(strict_types=1);

namespace App\Task\Provider;

use App\DateTimeProvider\DateTimeProvider;
use App\Task\Entity\Task;
use App\Task\Entity\TaskRecurrence;
use App\Task\Repository\TaskRecurrenceRepository;

readonly class TaskRecurrenceProvider
{
    public function __construct(
        private DateTimeProvider $dateTimeProvider,
        private TaskRecurrenceRepository $repository,
    ) {
    }

    public function getCurrentTaskRecurrence(Task $task): ?TaskRecurrence
    {
        return $this->repository->getCurrentByTask($task, $this->dateTimeProvider->getNow());
    }
}
