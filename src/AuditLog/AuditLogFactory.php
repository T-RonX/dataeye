<?php

declare(strict_types=1);

namespace App\AuditLog;

use App\AuditLog\Entity\AuditLog;
use App\AuditLog\Entity\AuditLogCollectionInsert;
use App\AuditLog\Entity\AuditLogCollectionRemove;
use App\AuditLog\Entity\AuditLogEntity;
use App\AuditLog\Entity\AuditLogFieldCreate;
use App\AuditLog\Entity\AuditLogFieldUpdate;
use App\AuditLog\Entity\AuditLogProperty;

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

    public function createAuditLogProperty(): AuditLogProperty
    {
        return new AuditLogProperty();
    }
    public function createAuditLogCollectionInsert(): AuditLogCollectionInsert
    {
        return new AuditLogCollectionInsert();
    }

    public function createAuditLogCollectionRemove(): AuditLogCollectionRemove
    {
        return new AuditLogCollectionRemove();
    }
}
