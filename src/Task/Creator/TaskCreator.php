<?php

declare(strict_types=1);

namespace App\Task\Creator;

use App\Task\Entity\Task;
use App\Task\Entity\TaskCategory;
use App\Task\Enum\RecurrenceType;
use App\Task\Factory\TaskFactory;
use App\Task\Recurrence\Recurrence;
use App\User\Entity\User;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class TaskCreator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskFactory $factory,
        private Recurrence $recurrence,
    ) {
    }

    /**
     * @param User[] $participatingUsers
     */
    public function create(User $owner, string $name, string $description, int $duration, ?TaskCategory $category, array $participatingUsers, DateTimeInterface $startsAt, ?DateTimeInterface $recurrenceEndsAt, ?RecurrenceType $recurrence, array $recurrenceParams = []): Task
    {
        $task = $this->createTask($owner, $name, $description, $duration, $category);

        $this->recurrence->setForTask($task, $startsAt, $recurrenceEndsAt, $recurrence, $recurrenceParams);
        $this->createParticipants($task, $participatingUsers);

        $this->entityManager->flush();

        return $task;
    }

    private function createTask(User $owner, string $name, string $description, int $duration, ?TaskCategory $category): Task
    {
        $task = $this->factory->createTask()
            ->setOwnedBy($owner)
            ->setName($name)
            ->setDescription($description)
            ->setDuration($duration)
            ->setCategory($category);

        $this->entityManager->persist($task);

        return $task;
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
