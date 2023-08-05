<?php

declare(strict_types=1);

namespace App\AuditLog\Event;

enum PropertyEvent
{
    case ON_CREATE;
    case ON_UPDATE;
    case ON_REMOVE;
}