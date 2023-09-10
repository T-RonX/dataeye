<?php

declare(strict_types=1);

namespace App\Task\Provider;

use App\Task\Entity\Task;
use App\Task\Entity\TaskDeferral;
use App\Task\Entity\TaskRecurrence;
use App\Task\Repository\TaskDeferralRepository;

readonly class TaskDeferralProvider
{
    public function __construct(
        private TaskDeferralRepository $repository,
    ) {
    }

    /**
     * @return TaskDeferral[]
     */
    public function getByTaskOrRecurrence(Task|TaskRecurrence $source/*, DateTimeInterface $lowerBound*/): array
    {
        return $this->repository->getByTaskOrRecurrence($source/*, $lowerBound*/);
    }
}
