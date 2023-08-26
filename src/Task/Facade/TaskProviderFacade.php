<?php

declare(strict_types=1);

namespace App\Task\Facade;

use App\Context\UserContext;
use App\Facade\FacadeInterface;
use App\Task\Entity\Task;
use App\Task\Provider\TaskProvider;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('task-provider')]
readonly class TaskProviderFacade implements FacadeInterface
{
    public function __construct(
        private UserContext $userContext,
        private TaskProvider $taskProvider,
    ) {
    }

    /**
     * @return Task[]
     */
    public function getTasksByCurrentUser(): array
    {
        return $this->taskProvider->getTasksByUser($this->userContext->getUser());
    }
}