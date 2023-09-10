<?php

declare(strict_types=1);

namespace App\Task\Postponer;

use App\DateTimeProvider\DateTimeProvider;
use App\Task\Entity\Task;
use App\Task\Entity\TaskParticipant;
use App\Task\Entity\TaskPostpone;
use App\Task\Entity\TaskRecurrence;
use App\Task\Entity\TaskSkip;
use App\Task\Enum\PostponeMethod;
use App\Task\Factory\TaskFactory;
use App\Task\Provider\TaskParticipantProvider;
use App\Task\Provider\TaskRecurrenceProvider;
use App\Task\Recurrence\RecurrenceCalculator;
use App\User\Entity\User;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

readonly class TaskPostponer
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskFactory $factory,
        private TaskParticipantProvider $participantProvider,
        private DateTimeProvider $dateTimeProvider,
        private TaskRecurrenceProvider $recurrenceProvider,
        private RecurrenceCalculator $recurrenceCalculator,
    ) {
    }

    public function postpone(Task $task, PostponeMethod $method, ?DateInterval $delay, User $user, DateTimeZone $timezone): void
    {
        $participant = $this->participantProvider->getTaskParticipantByUser($task, $user);
        $recurrence = $this->recurrenceProvider->getCurrentTaskRecurrence($task);
        $hasRecurrence = $recurrence !== null;

        if ($participant === null)
        {
            throw new RuntimeException('Task participant not found.');
        }

        if ($method === PostponeMethod::TimeDelay && $delay === null)
        {
            throw new RuntimeException("Can not postpone without delay.");
        }

        match ($hasRecurrence) {
            true => match($method) {
                PostponeMethod::TimeDelay => $this->postponeRecurrence($recurrence, $delay, $participant, $timezone),
                PostponeMethod::SkipOnce => $this->createSkip($recurrence, $participant),
            },
            false => match ($method) {
                PostponeMethod::TimeDelay => $this->postponeTask($task, $delay, $participant),
                PostponeMethod::SkipOnce => $this->createSkip($task, $participant),
            },
        };
    }

    private function postponeTask(Task $task, DateInterval $delay, TaskParticipant $participant): TaskPostpone
    {
        $nextOccurrence = $this->recurrenceCalculator->getRecurrence($task, $this->dateTimeProvider->createTimezoneUTC(), 1)[0] ?? null;
        $delayedTo = $this->calculateDelayedTo($nextOccurrence ?: $task->getDateTime(), $delay);

        return $this->createPostpone($task, $delayedTo, $participant);
    }

    private function postponeRecurrence(TaskRecurrence $recurrence, DateInterval $delay, TaskParticipant $participant, DateTimeZone $timezone): TaskPostpone|TaskSkip|null
    {
        $occurrences = $this->recurrenceCalculator->getRecurrence($recurrence->getTask(), $timezone, 2);
        $currentOccurrence = $occurrences[0] ?? null;
        $nextOccurrence = $occurrences[1] ?? null;
        $delayedTo = $this->calculateDelayedTo(max($this->dateTimeProvider->getNow($timezone), $currentOccurrence), $delay);
        $delayedIsInvalid = $delayedTo < $recurrence->getStartDate();

        if ($delayedIsInvalid)
        {
            return null;
        }

        if ($nextOccurrence !== null && $delayedTo >= $nextOccurrence)
        {
            return $this->createSkip($recurrence, $participant);
        }

        return $this->createPostpone($recurrence, $delayedTo, $participant);
    }

    private function createPostpone(Task|TaskRecurrence $source, DateTimeInterface $delayedTo, TaskParticipant $participant): TaskPostpone
    {
        $postpone = ($this->factory->createTaskPostpone())
            ->setTask($source instanceof Task ? $source : $source->getTask())
            ->setRecurrence($source instanceof TaskRecurrence ? $source : null)
            ->setDelayedTo($delayedTo)
            ->setDeferredAt($this->dateTimeProvider->getNow())
            ->setDeferredBy($participant);

        $this->entityManager->persist($postpone);

        return $postpone;
    }

    private function createSkip(Task|TaskRecurrence $source, TaskParticipant $participant): TaskSkip
    {
        $skip = ($this->factory->createTaskSkip())
            ->setTask($source instanceof Task ? $source : $source->getTask())
            ->setRecurrence($source instanceof TaskRecurrence ? $source : null)
            ->setDeferredAt($this->dateTimeProvider->getNow())
            ->setDeferredBy($participant);

        $this->entityManager->persist($skip);

        return $skip;
    }

    private function calculateDelayedTo(DateTimeInterface $sourceDate, DateInterval $delay): DateTimeInterface
    {
        $dateTime = $sourceDate instanceof DateTimeImmutable ? $sourceDate : DateTimeImmutable::createFromInterface($sourceDate);

        return $dateTime->add($delay)->setTimezone($this->dateTimeProvider->createTimezoneUTC());
    }
}
