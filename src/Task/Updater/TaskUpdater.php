<?php

declare(strict_types=1);

namespace App\Task\Updater;

use App\Collection\DoctrineCollectionUpdater;
use App\Locale\Entity\Timezone;
use App\Task\Entity\Task;
use App\Task\Entity\TaskCategory;
use App\Task\Entity\TaskParticipant;
use App\Task\Enum\RecurrenceType;
use App\Task\Factory\TaskFactory;
use App\Task\Provider\TaskParticipantProvider;
use App\Task\Recurrence\Recurrence;
use App\User\Entity\User;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class TaskUpdater
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskFactory $factory,
        private DoctrineCollectionUpdater $collectionUpdater,
        private Recurrence $recurrence,
        private TaskParticipantProvider $participantProvider,
    ) {
    }

    public function update(Task $task, string $name, string $description, int $duration, ?TaskCategory $category, array $participatingUsers, DateTimeInterface $dateTime, Timezone $timezone, ?DateTimeInterface $recurrenceStartDate, ?DateTimeInterface $recurrenceEndDate, ?RecurrenceType $recurrence, array $recurrenceParams = []): Task
    {
        $task->setName($name)
            ->setDateTime($dateTime)
            ->setTimezone($timezone)
            ->setDescription($description)
            ->setDuration($duration)
            ->setCategory($category);

        $this->recurrence->setForTask($task, $recurrenceStartDate, $recurrenceEndDate, $recurrence, $recurrenceParams);
        $this->updateParticipants($task, [$task->getOwnedBy(), ...$participatingUsers]);

        $this->entityManager->persist($task);

        return $task;
    }

    private function updateParticipants(Task $task, array $users): void
    {
        $update = $this->collectionUpdater->update(
            $this->participantProvider->getTaskParticipantsCollection($task),
            $users,
            static fn (TaskParticipant $participant): User => $participant->getUser(),
        );

        $this->createParticipants($task, $update->getAdded());
    }

    private function createParticipants(Task $task, array $users): void
    {
        foreach ($users as $user)
        {
            $this->createParticipant($task, $user);
        }
    }

    private function createParticipant(Task $task, User $user): void
    {
        $participant = ($this->factory->createTaskParticipant())
            ->setTask($task)
            ->setUser($user);

        $this->entityManager->persist($participant);
        $task->getParticipants()->add($participant);
    }
}