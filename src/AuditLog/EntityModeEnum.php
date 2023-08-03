<?php

declare(strict_types=1);

namespace App\AuditLog;

enum EntityModeEnum: int
{
    case Create = 1;
    case Update = 2;
    case Delete = 3;
}