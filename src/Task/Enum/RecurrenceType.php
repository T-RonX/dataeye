<?php

declare(strict_types=1);

namespace App\Task\Enum;

enum RecurrenceType: int
{
    case Day = 1;
    case Week = 2;
    case Month = 3;
    case Year = 4;

    public function toString(): string
    {
        return (string) $this->value;
    }
}