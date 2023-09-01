<?php

declare(strict_types=1);

namespace App\AuditLog;

enum EntityModeEnum: int
{
    case Create = 1;
    case Update = 2;
    case Delete = 3;
    case CollectionInsert = 4;
    case CollectionRemove = 5;
}