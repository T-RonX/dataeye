<?php

declare(strict_types=1);

namespace App\Task\Recurrence;

use App\DateTimeProvider\DateTimeProvider;
use App\Task\Entity\Task;
use App\Task\Enum\RecurrenceType;
use App\Task\Repository\TaskRecurrenceRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class Recurrence
{
    public function __construct(
        private RecurrenceCreator $creator,
        private EntityManagerInterface $entityManager,
        private TaskRecurrenceRepository $repository,
        private DateTimeProvider $dateTimeProvider,
    ) {
    }

    public function setForTask(Task $task, ?DateTimeInterface $startDate, ?DateTimeInterface $endDate, ?RecurrenceType $recurrence, array $params = []): void
    {
        $isNewTask = $this->entityManager->getUnitOfWork()->isScheduledForInsert($task);

        // @TODO: fix end dates
        $taskRecurrences = $task->getRecurrences(); //$this->repository->getByTaskOrderedByDate($task);
//        $ordered = [];

        foreach ($taskRecurrences as $taskRecurrence)
        {
//            $ordered[$taskRecurrence->getStartsAt()->format("YmdHis{$taskRecurrence->getId()}")] = $taskRecurrence;
            $taskRecurrence->setDeletedAt($this->dateTimeProvider->getNow());
            $this->entityManager->persist($taskRecurrence);
        }

        if ($recurrence !== null)
        {
            $startDate = $startDate ? $this->makeDatetimeImmutable($startDate) : null;
            $endDate = $endDate ? $this->makeDatetimeImmutable($endDate) : null;

            $newRecurrence = ($this->creator->create($recurrence, $params))
                ->setTask($task)
                ->setStartDate($startDate)
                ->setEndDate($endDate);

            $task->getRecurrences()->add($newRecurrence);
            $this->entityManager->persist($newRecurrence);
        }

        $this->entityManager->flush();
    }

    private function makeDatetimeImmutable(DateTimeInterface $dateTime): DateTimeImmutable
    {
       return $dateTime instanceof DateTimeImmutable ? $dateTime : DateTimeImmutable::createFromInterface($dateTime);
    }
}
