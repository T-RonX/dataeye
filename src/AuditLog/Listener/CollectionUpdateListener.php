<?php

declare(strict_types=1);

namespace App\AuditLog\Listener;

use App\AuditLog\AuditLog;
use App\AuditLog\RequiresLoggingTrait;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::onFlush)]
readonly class CollectionUpdateListener
{
    use RequiresLoggingTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AuditLog $auditLog,
    ) {
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $unitOfWork = $eventArgs->getObjectManager()->getUnitOfWork();

        foreach ($unitOfWork->getScheduledCollectionUpdates() as $collectionUpdate)
        {
            $owner = $collectionUpdate->getOwner();
            $ownerField = $collectionUpdate->getMapping()['fieldName'];

            if ($owner === null)
            {
                continue;
            }

            foreach ($collectionUpdate->getInsertDiff() as $addedEntity)
            {
                if ($this->requiresLogging($addedEntity))
                {
                    $this->auditLog->scheduleForCollectionInsert($owner, $ownerField, $addedEntity);
                }
            }

            foreach ($collectionUpdate->getDeleteDiff() as $deletedEntity)
            {
                if ($this->requiresLogging($deletedEntity))
                {
                    $this->auditLog->scheduleForCollectionRemoval($owner, $ownerField, $deletedEntity);
                }
            }
        }
    }
}
