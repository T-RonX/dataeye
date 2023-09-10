<?php

declare(strict_types=1);

namespace App\Task\Enum;

enum PostponeMethod: string
{
    case TimeDelay = 'time_delay';
    case SkipOnce = 'skip_once';
}
