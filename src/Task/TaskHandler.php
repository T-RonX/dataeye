<?php

declare(strict_types=1);

namespace App\Task;

use App\Context\UserContext;
use App\Facade\FacadeInterface;
use App\Task\Creator\TaskCreator;
use App\Task\Deleter\TaskDeleter;
use App\Task\Entity\Task;
use App\Task\Entity\TaskCategory;
use App\Task\Provider\TaskCategoryProvider;
use App\Task\Provider\TaskProvider;
use App\Task\Updater\TaskUpdater;
use App\User\Entity\User;
use App\User\Provider\UserProvider;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('task')]
readonly class TaskHandler implements FacadeInterface
{
    public function __construct(
        private UserContext $userContext,
        private TaskProvider $taskProvider,
        private UserProvider $userProvider,
        private TaskCreator $creator,
        private TaskUpdater $updater,
        private TaskDeleter $deleter,
        private TaskCategoryProvider $taskCategoryProvider,
        private EntityManagerInterface $entityManager,
    ) {
    }
    public function create(string $name, string $description, int $duration, TaskCategory|int $category, DateTime|string $recurrenceStartsAt, DateTime|string $recurrenceEndsAt, array|string $participatingUsers): Task
    {
        return $this->entityManager->wrapInTransaction(function() use($name, $description, $duration, $category, $recurrenceStartsAt, $recurrenceEndsAt, $participatingUsers): Task
        {
            $owner = $this->userContext->getUser();
            $category = $this->taskCategoryProvider->resolveTaskCategory($category);
            $recurrenceStartsAt = $recurrenceStartsAt instanceof DateTime ? $recurrenceStartsAt : new DateTime($recurrenceStartsAt);
            $recurrenceEndsAt = $recurrenceEndsAt instanceof DateTime ? $recurrenceEndsAt : new DateTime($recurrenceEndsAt);
            array_map(fn(User|int $participant) => $this->userProvider->resolveUser($participant), $participatingUsers);

            return $this->creator->create($owner, $name, $description, $duration, $category, $recurrenceStartsAt, $recurrenceEndsAt, $participatingUsers);
        });
    }

    public function update(Task|int $task, string $name): Task
    {
        return $this->entityManager->wrapInTransaction(function() use($task, $name): Task {
            $task = $this->taskProvider->resolveTask($task);

            return $this->updater->update($task, $name);
        });
    }
    public function delete(Task|int $task): void
    {
        $this->entityManager->wrapInTransaction(function() use($task): void {
            $task = $this->taskProvider->resolveTask($task);
            $this->deleter->delete($task);
        });
    }
}