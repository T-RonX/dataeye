<?php

declare(strict_types=1);

namespace App\Task\Enum;

enum RecurrenceField: string
{
    case DayInterval = 'day_interval';

    case WeekInterval = 'week_interval';
    case WeekDays = 'week_days';

    case MonthMode = 'month_mode';
    case MonthInterval = 'month_interval';
    case MonthAbsoluteDayNumber = 'month_day_number';
    case MonthRelativeWeekOrdinal = 'month_week_ordinal';
    case MonthRelativeDay = 'month_day';

    case YearMode = 'year_mode';
    case YearMonth = 'year_month';
    case YearAbsoluteDayNumber = 'year_day_number';
    case YearRelativeDayOrdinal = 'year_day_ordinal';
    case YearRelativeDay = 'year_day';

}