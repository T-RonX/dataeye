<?php

declare(strict_types=1);

namespace App\AuditLog\Listener;

use App\AuditLog\AuditLog;
use App\AuditLog\RequiresLoggingTrait;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist)]
readonly class EntityCreateListener
{
    use RequiresLoggingTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AuditLog $auditLog,
    ) {
    }

    public function postPersist(PostPersistEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();

        if (
            !$this->requiresLogging($entity)
            || !$this->auditLog->isScheduleForCreate($entity)
        ) {
            return;
        }

        $this->auditLog->logCreate($entity);
    }
}
