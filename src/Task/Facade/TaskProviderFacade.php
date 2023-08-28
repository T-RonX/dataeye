<?php

declare(strict_types=1);

namespace App\Task\Facade;

use App\Context\UserContext;
use App\Facade\FacadeInterface;
use App\Locale\Entity\Timezone;
use App\Task\Entity\Task;
use App\Task\Provider\TaskProvider;
use App\Task\Recurrence\RecurrenceCalculator;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('task-provider')]
readonly class TaskProviderFacade implements FacadeInterface
{
    public function __construct(
        private UserContext $userContext,
        private TaskProvider $taskProvider,
        private RecurrenceCalculator $recurrenceProvider,
    ) {
    }

    /**
     * @return Task[]
     */
    public function getTasksByCurrentUser(): array
    {
        return $this->taskProvider->getTasksByUser($this->userContext->getUser());
    }

    public function getRecurrence(Task|int $task, Timezone|string $timezone): array
    {
        $task = $this->taskProvider->resolveTask($task);

        return $this->recurrenceProvider->getRecurrence($task, $timezone);
    }
}