<?php

declare(strict_types=1);

namespace App\Task\Enum;

enum TaskRecurrenceMode: int
{
    case Absolute = 1;
    case Relative = 2;
}