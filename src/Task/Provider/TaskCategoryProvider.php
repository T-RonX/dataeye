<?php

declare(strict_types=1);

namespace App\Task\Provider;

use App\Exception\ItemNotFoundException;
use App\Task\Entity\TaskCategory;
use App\Task\Repository\TaskCategoryRepository;
use App\User\Entity\User;

readonly class TaskCategoryProvider
{
    public function __construct(
        private TaskCategoryRepository $repository,
    ) {
    }

    /**
     * @return array<TaskCategory>
     */
    public function getByOwner(User $user): array
    {
        return $this->repository->getByOwner($user);
    }

    public function getTaskCategory(TaskCategory|int $category): ?TaskCategory
    {
        if ($category instanceof TaskCategory)
        {
            return $category;
        }

        return $this->repository->find($category);
    }

    public function resolveTaskCategory(TaskCategory|int $item): TaskCategory
    {
        $category = $this->getTaskCategory($item);

        if ($category === null)
        {
            throw new ItemNotFoundException(TaskCategory::class, (string) $item);
        }

        return $category;
    }
}
