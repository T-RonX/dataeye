<?php

declare(strict_types=1);

namespace App\Task\Enum;

enum RecurrenceMode: int
{
    case Absolute = 1;
    case Relative = 2;
}