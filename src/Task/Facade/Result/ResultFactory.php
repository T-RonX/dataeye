<?php

declare(strict_types=1);

namespace App\Task\Facade\Result;

use App\Task\Entity\Task;
use DateTimeInterface;

readonly class ResultFactory
{
    /**
     * @param Task $task
     * @param DateTimeInterface[] $occurrences
     */
    public function createTaskOccurrences(Task $task, array $occurrences): TaskOccurrences
    {
        return new TaskOccurrences($task, $occurrences);
    }
}
