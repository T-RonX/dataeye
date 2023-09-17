<?php

declare(strict_types=1);

namespace App\Facades\Task;

use App\Context\UserContext;
use App\DateTimeProvider\DateTimeProvider;
use App\Facade\FacadeInterface;
use App\Facades\Task\Result\ResultFactory;
use App\Facades\Task\Result\TaskOccurrences;
use App\Locale\Entity\Timezone;
use App\Task\Entity\Task;
use App\Task\Provider\TaskProvider;
use App\Task\Recurrence\RecurrenceCalculator;
use App\UserPreference\Provider\UserPreferenceProvider;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('task-provider')]
readonly class TaskProviderFacade implements FacadeInterface
{
    public function __construct(
        private UserContext $userContext,
        private UserPreferenceProvider $userPreferenceProvider,
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

    /**
     * @return TaskOccurrences[]
     */
    public function getNextOccurrencesForCurrentUser(): array
    {
        $user = $this->userContext->getUser();
        $timezone = $this->userPreferenceProvider->getTimezone($user)->getTimezone();
        $dateTimezone = $this->dateTimeProvider->resolveDateTimeZone($timezone->getName());
        $now = $this->dateTimeProvider->getNow($dateTimezone);
        $tasks = $this->taskProvider->getTasksByUser($user);

        $occurrences = [];

        foreach ($tasks as $task)
        {
            $dates = $this->recurrenceCalculator->getRecurrence($task, $dateTimezone, 1, $now);
            $occurrences[] = $this->resultFactory->createTaskOccurrences($task, $dates);
        }

        return $occurrences;
    }

    public function getOccurrences(Task|int $task, Timezone|string $timezone, int|DateTimeInterface $limit, DateTimeInterface $lowerBound = null): TaskOccurrences
    {
        $task = $this->taskProvider->resolveTask($task);
        $dateTimezone = $this->dateTimeProvider->resolveDateTimeZone($timezone?->getName() ?: $timezone);
        $occurrences = $this->recurrenceCalculator->getRecurrence($task, $dateTimezone, $limit, $lowerBound);

        return $this->resultFactory->createTaskOccurrences($task, $occurrences);
    }
}