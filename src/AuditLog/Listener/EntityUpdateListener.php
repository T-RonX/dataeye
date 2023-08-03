<?php

declare(strict_types=1);

namespace App\AuditLog\Listener;

use App\AuditLog\AuditLog;
use App\AuditLog\RequiresLoggingTrait;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::preUpdate)]
readonly class EntityUpdateListener
{
    use RequiresLoggingTrait;

    public function __construct(
        private AuditLog $auditLog,
    ) {
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        if (!$this->requiresLogging($eventArgs->getObject()))
        {
            return;
        }

        $this->auditLog->logUpdate(
            $eventArgs->getObject(),
            $eventArgs->getEntityChangeSet(),
        );
    }
}
