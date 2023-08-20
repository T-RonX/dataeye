<?php

declare(strict_types=1);

namespace App\Task\Repository;

use App\Task\Entity\Task;
use App\Task\Entity\TaskRecurrence;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRecurrenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskRecurrence::class);
    }

    public function getByTaskAndDate(Task $task, DateTimeInterface $dateTime): ?TaskRecurrence
    {
        return $this->createQueryBuilder('i')
            ->where('i.task = :task')
            ->andWhere('i.startsAt <= :datetime AND (i.endsAt > :datetime OR i.endsAt IS NULL)')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('task', $task)
            ->setParameter('datetime', $dateTime)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function getCurrentByTask(Task $task): ?TaskRecurrence
    {
        return $this->createQueryBuilder('i')
            ->where('i.task = :task')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('task', $task)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return TaskRecurrence[]
     */
    public function getByTaskOrderedByDate(Task $task): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.task = :task')
            ->andWhere('i.deletedAt IS NULL')
            ->setParameter('task', $task)
            ->getQuery()
            ->getResult();
    }
}
