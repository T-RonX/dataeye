<?php

declare(strict_types=1);

namespace App\Task\Facade;

use App\DateTimeProvider\DateTimeProvider;
use App\Facade\FacadeInterface;
use App\Task\Entity\Task;
use App\Task\Enum\PostponeMethod;
use App\Task\Facade\Trait\UserTimezoneTrait;
use App\Task\Postponer\TaskPostponer;
use App\Task\Provider\TaskProvider;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('task-postponer')]
 class TaskPostponerFacade implements FacadeInterface
{
    use UserTimezoneTrait;

    public function __construct(
        readonly private EntityManagerInterface $entityManager,
        readonly private TaskProvider $taskProvider,
        readonly private TaskPostponer $postponer,
        readonly private DateTimeProvider $dateTimeProvider,
    ) {
    }

    public function postpone(Task|int $task, string|PostponeMethod $method, null|string|DateInterval $delay): void
    {
        $this->entityManager->wrapInTransaction(function() use ($task, $delay, $method): void {
            $task = $this->taskProvider->resolveTask($task);
            $method = $this->resolveDelayMethod($method);
            $delay = $delay !== null ? $this->resolveDelayToDate($delay) : null;
            $user = $this->userContext->getUser();
            $timezone = $this->dateTimeProvider->resolveDateTimeZone($this->getUserTimezone()->getName());

            $this->postponer->postpone($task, $method, $delay, $user, $timezone);
        });
    }

    private function resolveDelayToDate(string|DateInterval $delay): DateInterval
    {
        return $delay instanceof DateInterval ? $delay : new DateInterval($delay);
    }

    private function resolveDelayMethod(string|PostponeMethod $method): PostponeMethod
    {
        return $method instanceof PostponeMethod ? $method : PostponeMethod::from($method);
    }
}