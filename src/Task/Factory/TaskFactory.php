<?php

declare(strict_types=1);

namespace App\Task\Factory;

use App\Task\Entity\Task;
use App\Task\Entity\TaskParticipant;
use App\Task\Entity\TaskRecurrence;

readonly class TaskFactory
{
    public function createTask(): Task
    {
        return new Task();
    }

    public function createTaskRecurrence(): TaskRecurrence
    {
        return new TaskRecurrence();
    }

    public function createTaskParticipant(): TaskParticipant
    {
        return new TaskParticipant();
    }
}
