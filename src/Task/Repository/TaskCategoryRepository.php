<?php

declare(strict_types=1);

namespace App\Task\Repository;

use App\Task\Entity\TaskCategory;
use App\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskCategory::class);
    }

    /**
     * @return array<TaskCategory>
     */
    public function getByOwner(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.ownedBy = :owned_by')
            ->setParameter('owned_by', $user)
            ->getQuery()
            ->getResult();
    }
}