<?php

declare(strict_types=1);

namespace App\Task\Facade;

use App\Facade\FacadeInterface;
use App\Task\Entity\Task;
use App\Task\Entity\TaskCategory;
use App\Task\Enum\RecurrenceType;
use App\Task\Facade\Trait\ArgumentResolverTrait;
use App\Task\Facade\Trait\UserTimezoneTrait;
use App\Task\Provider\TaskProvider;
use App\Task\Updater\TaskUpdater;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('task-updater')]
class TaskUpdaterFacade implements FacadeInterface
{
    use ArgumentResolverTrait;
    use UserTimezoneTrait;

    public function __construct(
        readonly private TaskProvider $taskProvider,
        readonly private TaskUpdater $updater,
        readonly private EntityManagerInterface $entityManager,
    ) {
    }

    public function update(Task|int $task, string $name, string $description, int $duration, TaskCategory|int|null $category, array|string $participatingUsers, DateTimeInterface|string $dateTime, DateTimeInterface|string|null $recurrenceEndDate, RecurrenceType|int|null $taskRecurrence, array|null $recurrenceParams): Task
    {
        return $this->entityManager->wrapInTransaction(function() use($task, $name, $description, $duration, $category, $participatingUsers, $dateTime, $recurrenceEndDate, $taskRecurrence, $recurrenceParams): Task {
            $task = $this->taskProvider->resolveTask($task);
            $timezone = $this->getUserTimezone();

            [$category, $participatingUsers, $dateTime, $recurrenceStartDate, $recurrenceEndDate, $taskRecurrence, $recurrenceParams]
                = $this->resolveCommonValues($category, $participatingUsers, $dateTime, $recurrenceEndDate, $taskRecurrence, $recurrenceParams);
            $dateTime->getTimezone()->getLocation();

            return $this->updater->update($task, $name, $description, $duration, $category, $participatingUsers, $dateTime, $timezone, $recurrenceStartDate, $recurrenceEndDate, $taskRecurrence, $recurrenceParams);
        });
    }
}