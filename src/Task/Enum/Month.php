<?php

declare(strict_types=1);

namespace App\Task\Enum;

use Carbon\CarbonInterface;

enum Month: int
{
    case January = 1;
    case February = 2;
    case March = 3;
    case April = 4;
    case May = 5;
    case June = 6;
    case July = 7;
    case August = 8;
    case September = 9;
    case October = 10;
    case November = 11;
    case December = 12;

    public function toCarbon(): int
    {
        return match ($this) {
            self::January => CarbonInterface::JANUARY,
            self::February => CarbonInterface::FEBRUARY,
            self::March => CarbonInterface::MARCH,
            self::April => CarbonInterface::APRIL,
            self::May => CarbonInterface::MAY,
            self::June => CarbonInterface::JUNE,
            self::July => CarbonInterface::JULY,
            self::August => CarbonInterface::AUGUST,
            self::September => CarbonInterface::SEPTEMBER,
            self::October => CarbonInterface::OCTOBER,
            self::November => CarbonInterface::NOVEMBER,
            self::December => CarbonInterface::DECEMBER,
        };
    }
}
