<?php

declare(strict_types=1);

namespace App\Forms\Enum;

enum TaskRecurrence: int
{
    case Day = 1;
    case Week = 2;
    case Month = 3;
    case Year = 4;
}