<?php

declare(strict_types=1);

namespace App\Task\Provider;

use App\Task\Entity\Task;
use App\Task\Entity\TaskParticipant;
use App\Task\Repository\TaskParticipantRepository;
use App\User\Entity\User;

readonly class TaskParticipantProvider
{
    public function __construct(
        private TaskParticipantRepository $repository,
    ) {
    }

    /**
     * @return array<User>
     */
    public function getTaskParticipantUsers(Task $task): array
    {
        return array_map(static fn(TaskParticipant $participant) => $participant->getUser(), $this->getTaskParticipants($task));
    }

    /**
     * @return array<TaskParticipant>
     */
    public function getTaskParticipants(Task $task): array
    {
        return $this->repository->getByTask($task);
    }
}
