<?php

declare(strict_types=1);

namespace App\Task\Updater;

use App\Collection\DoctrineCollectionUpdater;
use App\Task\Entity\Task;
use App\Task\Entity\TaskCategory;
use App\Task\Entity\TaskParticipant;
use App\Task\Factory\TaskFactory;
use App\User\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

readonly class TaskUpdater
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskFactory $factory,
        private DoctrineCollectionUpdater $collectionUpdater,
    ) {
    }
    public function update(Task $task, string $name, string $description, int $duration, TaskCategory $category, DateTime $recurrenceStartsAt, DateTime $recurrenceEndsAt, array $participatingUsers): Task
    {
        $task->setName($name)
            ->setDescription($description)
            ->setDuration($duration)
            ->setCategory($category);

        $this->updateParticipants($task, [$task->getOwnedBy(), ...$participatingUsers]);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    private function updateParticipants(Task $task, array $users): void
    {
        $update = $this->collectionUpdater->update(
            $task->getParticipants(),
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