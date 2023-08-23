<?php

declare(strict_types=1);

namespace App\Task\Repository;

use App\Task\Entity\Task;
use App\Task\Entity\TaskParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskParticipant::class);
    }

    /**
     * @return array<TaskParticipant>
     */
    public function getByTask(Task $task): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.task = :task')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('task', $task)
            ->getQuery()
            ->getResult();
    }
}
