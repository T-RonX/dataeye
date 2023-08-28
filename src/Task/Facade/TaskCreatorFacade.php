<?php

declare(strict_types=1);

namespace App\Task\Facade;

use App\Facade\FacadeInterface;
use App\Task\Creator\TaskCreator;
use App\Task\Entity\Task;
use App\Task\Entity\TaskCategory;
use App\Task\Enum\RecurrenceType;
use App\Task\Facade\Trait\ArgumentResolverTrait;
use App\Task\Facade\Trait\UserTimezoneTrait;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('task-creator')]
class TaskCreatorFacade implements FacadeInterface
{
    use ArgumentResolverTrait;
    use UserTimezoneTrait;

    public function __construct(
        readonly private TaskCreator $creator,
        readonly private EntityManagerInterface $entityManager,
    ) {
    }
    public function create(string $name, string $description, int $duration, TaskCategory|int|null $category, array|string $participatingUsers, DateTimeInterface|string $dateTime, DateTimeInterface|string|null $recurrenceEndDate, RecurrenceType|int|null $taskRecurrence, array|null $recurrenceParams): Task
    {
        return $this->entityManager->wrapInTransaction(function() use($name, $description, $duration, $category, $participatingUsers, $dateTime, $recurrenceEndDate, $taskRecurrence, $recurrenceParams): Task {
            $owner = $this->userContext->getUser();
            $timezone = $this->getUserTimezone();

            [$category, $participatingUsers, $dateTime, $recurrenceStartDate, $recurrenceEndDate, $taskRecurrence, $recurrenceParams]
                = $this->resolveCommonValues($category, $participatingUsers, $dateTime, $recurrenceEndDate, $taskRecurrence, $recurrenceParams);

            return $this->creator->create($owner, $name, $description, $duration, $category, $participatingUsers, $dateTime, $timezone, $recurrenceStartDate, $recurrenceEndDate, $taskRecurrence, $recurrenceParams);
        });
    }
}
