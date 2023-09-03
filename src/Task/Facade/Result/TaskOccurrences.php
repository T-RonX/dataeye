<?php

declare(strict_types=1);

namespace App\Task\Facade\Result;

use App\Task\Entity\Task;
use DateTimeInterface;

readonly class TaskOccurrences
{
    /**
     * @param Task $task
     * @param DateTimeInterface[] $occurrences
     */
    public function __construct(
        private Task $task,
        private array $occurrences
    ) {
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function getOccurrences(): array
    {
        return $this->occurrences;
    }
}
