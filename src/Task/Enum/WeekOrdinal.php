<?php

declare(strict_types=1);

namespace App\Task\Enum;

enum WeekOrdinal: int
{
    case First = 1;
    case Second = 2;
    case Third = 3;
    case Fourth = 4;
    case Last = 5;
}
