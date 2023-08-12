<?php

declare(strict_types=1);

namespace App\Task\Repository;

use App\Task\Entity\Task;
use App\Task\Entity\TaskRecurrence;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRecurrenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskRecurrence::class);
    }

    public function getByTaskAndDate(Task $task, DateTime $dateTime): ?TaskRecurrence
    {
        return $this->createQueryBuilder('i')
            ->where('i.task = :task')
            ->andWhere('i.startsAt <= :datetime AND (i.endsAt > :datetime OR i.endsAt IS NULL)')
            ->setParameter('task', $task)
            ->setParameter('datetime', $dateTime)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
