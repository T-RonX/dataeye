<?php

declare(strict_types=1);

namespace App\Task\Completer;

use App\DateTimeProvider\DateTimeProvider;
use App\Task\Entity\Task;
use App\Task\Entity\TaskParticipant;
use App\Task\Entity\TaskRecurrence;
use App\Task\Factory\TaskFactory;
use App\Task\Provider\TaskParticipantProvider;
use App\Task\Provider\TaskRecurrenceProvider;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

readonly class TaskCompleter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DateTimeProvider $dateTimeProvider,
        private TaskFactory $factory,
        private TaskParticipantProvider $participantProvider,
        private TaskRecurrenceProvider $recurrenceProvider,
    ) {
    }

    public function complete(Task $task, User $user): void
    {
        $participant = $this->participantProvider->getTaskParticipantByUser($task, $user);
        $recurrence = $this->recurrenceProvider->getCurrentTaskRecurrence($task);

        if ($participant === null)
        {
            throw new RuntimeException('Task participant not found.');
        }

        $this->createCompletion($recurrence ?: $task, $participant);
    }

    private function createCompletion(Task|TaskRecurrence $source, TaskParticipant $participant): void
    {
        $completion = ($this->factory->createTaskCompletion())
            ->setTask($source instanceof Task ? $source : $source->getTask())
            ->setRecurrence($source instanceof TaskRecurrence ? $source : null)
            ->setCompletionBy($participant)
            ->setCompletionAt($this->dateTimeProvider->getNow());

        $this->entityManager->persist($completion);
    }
}