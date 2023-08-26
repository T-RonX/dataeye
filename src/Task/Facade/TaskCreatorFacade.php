<?php

declare(strict_types=1);

namespace App\Task\Facade;

use App\Context\UserContext;
use App\Facade\FacadeInterface;
use App\Task\Creator\TaskCreator;
use App\Task\Entity\Task;
use App\Task\Entity\TaskCategory;
use App\Task\Enum\RecurrenceType;
use App\Task\Facade\Trait\ArgumentResolverTrait;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('task-creator')]
class TaskCreatorFacade implements FacadeInterface
{
    use ArgumentResolverTrait;

    public function __construct(
        readonly private UserContext $userContext,
        readonly private TaskCreator $creator,
        readonly private EntityManagerInterface $entityManager,
    ) {
    }
    public function create(string $name, string $description, int $duration, TaskCategory|int|null $category, array|string $participatingUsers, DateTimeInterface|string $startsAt, DateTimeInterface|string|null $recurrenceEndsAt, RecurrenceType|int|null $taskRecurrence, array|null $recurrenceParams): Task
    {
        return $this->entityManager->wrapInTransaction(function() use($name, $description, $duration, $category, $participatingUsers, $startsAt, $recurrenceEndsAt, $taskRecurrence, $recurrenceParams): Task
        {
            $owner = $this->userContext->getUser();

            [$category, $participatingUsers, $startsAt, $recurrenceEndsAt, $taskRecurrence, $recurrenceParams]
                = $this->resolveCommonValues($category, $participatingUsers, $startsAt, $recurrenceEndsAt, $taskRecurrence, $recurrenceParams);

            return $this->creator->create($owner, $name, $description, $duration, $category, $participatingUsers, $startsAt, $recurrenceEndsAt, $taskRecurrence, $recurrenceParams);
        });
    }
}
