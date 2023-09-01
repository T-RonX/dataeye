<?php

declare(strict_types=1);

namespace App\AuditLog\Listener;

use App\AuditLog\AuditLog;
use App\AuditLog\RequiresLoggingTrait;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postFlush)]
readonly class CollectionFlushListener
{
    use RequiresLoggingTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AuditLog $auditLog,
    ) {
    }

    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        if ($this->auditLog->hasCollectionUpdatesScheduled())
        {
            $this->auditLog->handleCollectionUpdatesSchedule();
        }
    }
}
