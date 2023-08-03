<?php

declare(strict_types=1);

namespace App\AuditLog;

use App\AuditLog\Entity\AuditLog;
use App\AuditLog\Entity\AuditLogEntity;
use App\AuditLog\Entity\AuditLogFieldCreate;
use App\AuditLog\Entity\AuditLogFieldUpdate;

readonly class AuditLogFactory
{
    public function createAuditLog(): AuditLog
    {
        return new AuditLog();
    }

    public function createAuditLogFieldCreate(): AuditLogFieldCreate
    {
        return new AuditLogFieldCreate();
    }

    public function createAuditLogFieldUpdate(): AuditLogFieldUpdate
    {
        return new AuditLogFieldUpdate();
    }

    public function createAuditLogEntity(): AuditLogEntity
    {
        return new AuditLogEntity();
    }
}
