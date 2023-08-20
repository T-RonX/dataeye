<?php

declare(strict_types=1);

namespace App\Task\Recurrence;

use App\Task\Entity\TaskRecurrence as TaskRecurrenceEntity;
use App\Task\Entity\TaskRecurrenceDay;
use App\Task\Entity\TaskRecurrenceMonthAbsolute;
use App\Task\Entity\TaskRecurrenceMonthRelative;
use App\Task\Entity\TaskRecurrenceWeek;
use App\Task\Entity\TaskRecurrenceYearAbsolute;
use App\Task\Entity\TaskRecurrenceYearRelative;
use App\Task\Enum\RecurrenceField;
use App\Task\Enum\RecurrenceMode;
use App\Task\Enum\RecurrenceType;
use App\Task\Factory\TaskFactory;

readonly class RecurrenceCreator
{
    public function __construct(
        private TaskFactory $factory,
    ) {
    }

    public function create(RecurrenceType $recurrence, array $params): TaskRecurrenceEntity
    {
        return match ($recurrence) {
            RecurrenceType::Day => $this->createDayRecurrence($params),
            RecurrenceType::Week => $this->createWeekRecurrence($params),
            RecurrenceType::Month => match ($params[RecurrenceField::MonthMode->value]) {
                RecurrenceMode::Absolute => $this->createMonthAbsoluteRecurrence($params),
                RecurrenceMode::Relative => $this->createMonthRelativeRecurrence($params),
            },
            RecurrenceType::Year => match ($params[RecurrenceField::YearMode->value]) {
                RecurrenceMode::Absolute => $this->createYearAbsoluteRecurrence($params),
                RecurrenceMode::Relative => $this->createYearRelativeRecurrence($params),
            },
        };
    }

    private function createDayRecurrence(array $params): TaskRecurrenceDay
    {
        return $this->factory->createTaskRecurrenceDay()
            ->setInterval($params[RecurrenceField::DayInterval->value]);
    }

    private function createWeekRecurrence(array $params): TaskRecurrenceWeek
    {
        return $this->factory->createTaskRecurrenceWeek()
            ->setInterval($params[RecurrenceField::WeekInterval->value])
            ->setDays($params[RecurrenceField::WeekDays->value]);
    }

    private function createMonthAbsoluteRecurrence(array $params): TaskRecurrenceMonthAbsolute
    {
        return $this->factory->createTaskRecurrenceMonthAbsolute()
            ->setInterval($params[RecurrenceField::MonthInterval->value])
            ->setDayNumber($params[RecurrenceField::MonthAbsoluteDayNumber->value]);
    }

    private function createMonthRelativeRecurrence(array $params): TaskRecurrenceMonthRelative
    {
        return $this->factory->createTaskRecurrenceMonthRelative()
            ->setInterval($params[RecurrenceField::MonthInterval->value])
            ->setWeekOrdinal($params[RecurrenceField::MonthRelativeWeekOrdinal->value])
            ->setDay($params[RecurrenceField::MonthRelativeDay->value]);
    }

    private function createYearAbsoluteRecurrence(array $params): TaskRecurrenceYearAbsolute
    {
        return $this->factory->createTaskRecurrenceYearAbsolute()
            ->setMonth($params[RecurrenceField::YearMonth->value])
            ->setDayNumber($params[RecurrenceField::YearAbsoluteDayNumber->value]);
    }

    private function createYearRelativeRecurrence(array $params): TaskRecurrenceYearRelative
    {
        return $this->factory->createTaskRecurrenceYearRelative()
            ->setDayOrdinal($params[RecurrenceField::YearRelativeDayOrdinal->value])
            ->setDay($params[RecurrenceField::YearRelativeDay->value])
            ->setMonth($params[RecurrenceField::YearMonth->value]);
    }
}
