<?php

declare(strict_types=1);

namespace App\Task;

use App\Exception\ItemNotFoundException;
use App\Facade\FacadeInterface;
use App\Task\Creator\TaskCreator;
use App\Task\Deleter\TaskDeleter;
use App\Task\Entity\Task;
use App\Task\Provider\TaskProvider;
use App\Task\Updater\TaskUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('task')]
readonly class TaskHandler implements FacadeInterface
{
    public function __construct(
        private TaskProvider $provider,
        private TaskCreator $creator,
        private TaskUpdater $updater,
        private TaskDeleter $deleter,
        private EntityManagerInterface $entityManager,
    ) {
    }
    public function create(string $name): Task
    {
        return $this->entityManager->wrapInTransaction(function() use($name): Task {
            return $this->creator->create($name);
        });
    }

    public function update(Task|int $task, string $name): Task
    {
        return $this->entityManager->wrapInTransaction(function() use($task, $name): Task {
            $task = $this->resolveTask($task);

            return $this->updater->update($task, $name);
        });
    }
    public function delete(Task|int $task): void
    {
        $this->entityManager->wrapInTransaction(function() use($task): void {
            $task = $this->resolveTask($task);
            $this->deleter->delete($task);
        });
    }

    private function resolveTask(Task|int $task): Task
    {
        $task = $this->provider->getTask($task);

        if ($task === null)
        {
            throw new ItemNotFoundException(Task::class, (string) $task);
        }

        return $task;
    }
}