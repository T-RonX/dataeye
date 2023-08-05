<?php

declare(strict_types=1);

namespace App\AuditLog\Repository;

use App\AuditLog\Entity\AuditLogEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AuditLogEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditLogEntity::class);
    }

    public function getByEntityClass(string $entityName): ?AuditLogEntity
    {
        return $this->createQueryBuilder('e')
            ->where('e.entity = :entity_name')
            ->setParameter('entity_name', $entityName)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
