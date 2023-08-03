<?php

declare(strict_types=1);

namespace App\AuditLog\Listener;

use App\AuditLog\AuditLog;
use App\AuditLog\RequiresLoggingTrait;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;

#[AsDoctrineListener(event: Events::prePersist)]
readonly class EntityCreateCacheListener
{
    use RequiresLoggingTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AuditLog $auditLog,
    ) {
    }

    public function prePersist(PrePersistEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();

        if (
            !$this->requiresLogging($entity)
            || !$this->isNewEntity($entity)
        ) {
            return;
        }

        $this->auditLog->scheduleForCreate($entity);
    }

    private function isNewEntity(object $entity): bool
    {
        return $this->entityManager->getUnitOfWork()->getEntityState($entity) === UnitOfWork::STATE_NEW;
    }
}
