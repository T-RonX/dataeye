<?php

declare(strict_types=1);

namespace App\AuditLog;

trait RequiresLoggingTrait
{
    private function requiresLogging(object $entity): bool
    {
        $namespace = __NAMESPACE__.'\\Entity\\';

        return !str_starts_with($entity::class, $namespace);
    }
}
