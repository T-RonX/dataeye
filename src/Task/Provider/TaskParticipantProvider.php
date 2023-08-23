<?php

declare(strict_types=1);

namespace App\Task\Provider;

use App\Task\Entity\Task;
use App\Task\Entity\TaskParticipant;
use App\Task\Repository\TaskParticipantRepository;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

readonly class TaskParticipantProvider
{
    public function __construct(
        private TaskParticipantRepository $repository,
    ) {
    }

    /**
     * @return User[]
     */
    public function getTaskParticipantUsers(Task $task): array
    {
        return array_map(static fn(TaskParticipant $participant) => $participant->getUser(), $this->getTaskParticipants($task));
    }

    /**
     * @return TaskParticipant[]
     */
    public function getTaskParticipants(Task $task): array
    {
        return $this->repository->getByTask($task);
    }

    /**
     * @return ArrayCollection<int, TaskParticipant>
     */
    public function getTaskParticipantsCollection(Task $task): ArrayCollection
    {
        return new ArrayCollection($this->getTaskParticipants($task));
    }
}
