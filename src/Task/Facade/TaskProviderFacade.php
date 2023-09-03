<?php

declare(strict_types=1);

namespace App\Task\Facade;

use App\Context\UserContext;
use App\DateTimeProvider\DateTimeProvider;
use App\Facade\FacadeInterface;
use App\Locale\Entity\Timezone;
use App\Task\Entity\Task;
use App\Task\Facade\Result\ResultFactory;
use App\Task\Facade\Result\TaskOccurrences;
use App\Task\Provider\TaskProvider;
use App\Task\Recurrence\RecurrenceCalculator;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('task-provider')]
readonly class TaskProviderFacade implements FacadeInterface
{
    public function __construct(
        private UserContext $userContext,
        private TaskProvider $taskProvider,
        private RecurrenceCalculator $recurrenceCalculator,
        private DateTimeProvider $dateTimeProvider,
        private ResultFactory $resultFactory,
    ) {
    }

    /**
     * @return Task[]
     */
    public function getTasksByCurrentUser(): array
    {
        return $this->taskProvider->getTasksByUser($this->userContext->getUser());
    }

    public function getOccurrences(Task|int $task, Timezone|string $timezone, int|DateTimeInterface $limit): TaskOccurrences
    {
        $task = $this->taskProvider->resolveTask($task);
        $dateTimezone = $this->dateTimeProvider->resolveDateTimeZone($timezone?->getName() ?: $timezone);
        $occurrences = $this->recurrenceCalculator->getRecurrence($task, $dateTimezone, $limit);

        return $this->resultFactory->createTaskOccurrences($task, $occurrences);
    }
}