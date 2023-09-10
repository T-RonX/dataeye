<?php

declare(strict_types=1);

namespace App\Task\Repository;

use App\Task\Entity\Task;
use App\Task\Entity\TaskParticipant;
use App\User\Entity\User;
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

    public function getByTaskAndUser(Task $task, User $user): ?TaskParticipant
    {
        return $this->createQueryBuilder('p')
            ->where('p.task = :task')
            ->andWhere('p.deletedAt IS NULL')
            ->andWhere('p.user = :user')
            ->setParameter('task', $task)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
