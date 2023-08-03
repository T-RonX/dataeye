<?php

declare(strict_types=1);

namespace App\AuditLog\Listener;

use App\AuditLog\AuditLog;
use App\AuditLog\RequiresLoggingTrait;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postFlush)]
readonly class FlushAuditLogListener
{
    use RequiresLoggingTrait;

    public function __construct(
        private AuditLog $auditLog,
    ) {
    }

    public function postFlush(): void
    {
        if (!$this->auditLog->isFlushRequired())
        {
            return;
        }

        $this->auditLog->flush();
    }
}
