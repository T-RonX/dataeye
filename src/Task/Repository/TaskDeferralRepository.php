<?php

declare(strict_types=1);

namespace App\Task\Repository;

use App\Task\Entity\Task;
use App\Task\Entity\TaskDeferral;
use App\Task\Entity\TaskRecurrence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class TaskDeferralRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskDeferral::class);
    }

    /**
     * @return TaskDeferral[]
     */
    public function getByTaskOrRecurrence(Task|TaskRecurrence $source/*, DateTimeInterface $lowerBound*/): array
    {
        return match (true) {
            $source instanceof Task => $this->getByTask($source/*, $lowerBound*/),
            default => $this->getByRecurrence($source/*, $lowerBound*/),
        };
    }

    /**
     * @return TaskDeferral[]
     */
    public function getByTask(Task $task/*, DateTimeInterface $lowerBound*/): array
    {
        return $this->createLowerBoundQueryBuilder(/*$lowerBound*/)
            ->andWhere('d.task = :task')
            ->setParameter('task', $task)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return TaskDeferral[]
     */
    public function getByRecurrence(TaskRecurrence $recurrence/*, DateTimeInterface $lowerBound*/): array
    {
        return $this->createLowerBoundQueryBuilder(/*$lowerBound*/)
            ->andWhere('d.recurrence = :recurrence')
            ->setParameter('recurrence', $recurrence)
            ->getQuery()
            ->getResult();
    }

    private function createLowerBoundQueryBuilder(/*DateTimeInterface $lowerBound*/): QueryBuilder
    {
        return $this->createQueryBuilder('d')
//            ->where('d.delayedTo > :lowerBound')
//            ->leftJoin(TaskPostpone::class, 'p', Join::WITH, 'p = d')
//            ->setParameter('lowerBound', $lowerBound)
            ;
    }
}
