<?php

declare(strict_types=1);

namespace App\Task\Provider;

use App\Exception\ItemNotFoundException;
use App\Task\Entity\Task;
use App\Task\Repository\TaskRepository;
use App\User\Entity\User;

readonly class TaskProvider
{
    public function __construct(
        private TaskRepository $repository,
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

    /**
     * @return Task[]
     */
    public function getAllTask(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @return Task[]
     */
    public function getTaskByUser(User $user): array
    {
        return $this->repository->getByUser($user);
    }

    public function resolveTask(Task|int $item): Task
    {
        $task = $this->getTask($item);

        if ($task === null)
        {
            throw new ItemNotFoundException(Task::class, (string) $item);
        }

        return $task;
    }
}
