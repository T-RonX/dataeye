<?php

declare(strict_types=1);

namespace App\Task\Factory;

use App\Task\Entity\Task;
use App\Task\Entity\TaskParticipant;
use App\Task\Entity\TaskRecurrenceDay;
use App\Task\Entity\TaskRecurrenceMonthAbsolute;
use App\Task\Entity\TaskRecurrenceMonthRelative;
use App\Task\Entity\TaskRecurrenceWeek;
use App\Task\Entity\TaskRecurrenceYearAbsolute;
use App\Task\Entity\TaskRecurrenceYearRelative;

readonly class TaskFactory
{
    public function createTask(): Task
    {
        return new Task();
    }

    public function createTaskRecurrenceDay(): TaskRecurrenceDay
    {
        return new TaskRecurrenceDay();
    }

    public function createTaskRecurrenceWeek(): TaskRecurrenceWeek
    {
        return new TaskRecurrenceWeek();
    }

    public function createTaskRecurrenceMonthAbsolute(): TaskRecurrenceMonthAbsolute
    {
        return new TaskRecurrenceMonthAbsolute();
    }

    public function createTaskRecurrenceMonthRelative(): TaskRecurrenceMonthRelative
    {
        return new TaskRecurrenceMonthRelative();
    }

    public function createTaskRecurrenceYearAbsolute(): TaskRecurrenceYearAbsolute
    {
        return new TaskRecurrenceYearAbsolute();
    }

    public function createTaskRecurrenceYearRelative(): TaskRecurrenceYearRelative
    {
        return new TaskRecurrenceYearRelative();
    }

    public function createTaskParticipant(): TaskParticipant
    {
        return new TaskParticipant();
    }
}
