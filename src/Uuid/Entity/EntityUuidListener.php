<?php

declare(strict_types=1);

namespace App\Uuid\Entity;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Ramsey\Uuid\Uuid;

#[AsDoctrineListener(event: Events::prePersist)]
class EntityUuidListener
{
    public function prePersist(PrePersistEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();

        if (
            $entity instanceof EntityUuidInterface
            && !$entity->hasUuid()
        ) {
            $entity->setUuid(Uuid::uuid4()->toString());
        }
    }
}
