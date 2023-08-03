<?php

declare(strict_types=1);

namespace App\AuditLog\Listener;

use App\AuditLog\AuditLog;
use App\AuditLog\RequiresLoggingTrait;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::preRemove)]
readonly class EntityRemoveListener
{
    use RequiresLoggingTrait;

    public function __construct(
        private AuditLog $auditLog,
    ) {
    }

    public function preRemove(PreRemoveEventArgs $eventArgs): void
    {
        if (!$this->requiresLogging($eventArgs->getObject()))
        {
            return;
        }

        $this->auditLog->logRemove($eventArgs->getObject());
    }


}
