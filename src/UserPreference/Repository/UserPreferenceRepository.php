<?php

declare(strict_types=1);

namespace App\UserPreference\Repository;

use App\User\Entity\User;
use App\UserPreference\Entity\UserPreference;
use App\UserPreference\Entity\UserPreferenceTimezone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserPreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPreference::class);
    }

    public function getTimezone(User $user): UserPreferenceTimezone
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p INSTANCE OF '.UserPreferenceTimezone::class)
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}