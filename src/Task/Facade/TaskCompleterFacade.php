<?php

declare(strict_types=1);

namespace App\Task\Facade;

use App\Context\UserContext;
use App\Facade\FacadeInterface;
use App\Task\Completer\TaskCompleter;
use App\Task\Entity\Task;
use App\Task\Provider\TaskProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('task-completer')]
readonly class TaskCompleterFacade implements FacadeInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskProvider $taskProvider,
        private TaskCompleter $completer,
        private UserContext $userContext,
    ) {
    }

    public function complete(Task|int $task): void
    {
        $this->entityManager->wrapInTransaction(function() use($task): void {
            $task = $this->taskProvider->resolveTask($task);
            $user = $this->userContext->getUser();

            $this->completer->complete($task, $user);
        });
    }
}