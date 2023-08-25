<?php

declare(strict_types=1);

namespace App\Task\Enum;

use Carbon\CarbonInterface;

enum Day: int
{
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;
    case Sunday = 7;

    public function toCarbon(): int
    {
        return match ($this) {
            self::Monday => CarbonInterface::MONDAY,
            self::Tuesday => CarbonInterface::TUESDAY,
            self::Wednesday => CarbonInterface::WEDNESDAY,
            self::Thursday => CarbonInterface::THURSDAY,
            self::Friday => CarbonInterface::FRIDAY,
            self::Saturday => CarbonInterface::SATURDAY,
            self::Sunday => CarbonInterface::SUNDAY,
        };
    }

    /**
     * @return Day[]
     */
    public static function getWeekDaysMondayFirst(): array
    {
        return [
            self::Monday,
            self::Tuesday,
            self::Wednesday,
            self::Thursday,
            self::Friday,
            self::Saturday,
            self::Sunday,
        ];
    }

    /**
     * @return Day[]
     */
    public static function getWeekDaysSundayFirst(): array
    {
        return [
            self::Sunday,
            self::Monday,
            self::Tuesday,
            self::Wednesday,
            self::Thursday,
            self::Friday,
            self::Saturday,
        ];
    }
}
