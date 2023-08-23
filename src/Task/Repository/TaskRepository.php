<?php

declare(strict_types=1);

namespace App\Task\Repository;

use App\Task\Entity\Task;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return User[]
     */
    public function getByUser(User $user): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.ownedBy = :owner')
            ->setParameter('owner', $user)
            ->getQuery()
            ->getResult();
    }
}