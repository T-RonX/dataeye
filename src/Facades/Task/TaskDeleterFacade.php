<?php

declare(strict_types=1);

namespace App\Facades\Task;

use App\CliAccess\CliAccessInterface;
use App\Task\Deleter\TaskDeleter;
use App\Task\Entity\Task;
use App\Task\Provider\TaskProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('task-deleter')]
readonly class TaskDeleterFacade implements CliAccessInterface
{
    public function __construct(
        private TaskProvider $taskProvider,
        private TaskDeleter $deleter,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function delete(Task|int $task): void
    {
        $this->entityManager->wrapInTransaction(function() use($task): void {
            $task = $this->taskProvider->resolveTask($task);
            $this->deleter->delete($task);
        });
    }
}