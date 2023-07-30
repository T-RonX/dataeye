<?php

declare(strict_types=1);

namespace App\Task\Provider;

use App\Task\Entity\Task;
use App\Task\Repository\TaskRepository;

class TaskProvider
{
    public function __construct(
        private readonly TaskRepository $repository,
    ) {
    }

    public function getTask(Task|int $task): ?Task
    {
        if ($task instanceof Task)
        {
            return $task;
        }

        return $this->repository->find($task);
    }

    public function createNewTask(): Task
    {
        return new Task();
    }

    /**
     * @return Task[]
     */
    public function getAllTask(): array
    {
        return $this->repository->findAll();
    }
}
