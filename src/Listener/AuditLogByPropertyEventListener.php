<?php

declare(strict_types=1);

namespace App\Listener;

use App\AuditLog\Event\PropertyEvent;
use App\AuditLog\Event\PropertyEventArgs;
use App\Context\UserContext;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class AuditLogByPropertyEventListener
{
    public function __construct(
        private UserContext $userContext
    ) {
    }

    #[AsEventListener(event: PropertyEvent::ON_CREATE->name)]
    public function onCreate(PropertyEventArgs $eventArgs): void
    {
        $this->addByProperty($eventArgs);
    }

    #[AsEventListener(event: PropertyEvent::ON_UPDATE->name)]
    public function onUpdate(PropertyEventArgs $eventArgs): void
    {
        $this->addByProperty($eventArgs);
    }

    #[AsEventListener(event: PropertyEvent::ON_REMOVE->name)]
    public function onDelete(PropertyEventArgs $eventArgs): void
    {
        $this->addByProperty($eventArgs);
    }

    private function addByProperty(PropertyEventArgs $eventArgs): void
    {
        if ($this->userContext->hasUser())
        {
            $eventArgs->addProperty('by', (string)$this->userContext->getUser()->getId());
        }
    }
}
