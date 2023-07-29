<?php

declare(strict_types=1);

namespace App\Task\Creator;

use App\Facade\FacadeInterface;
use App\Task\Entity\Task;
use App\Task\Provider\TaskProvider;
use Doctrine\ORM\EntityManagerInterface;

readonly class TaskCreator implements FacadeInterface
{
    public function __construct(
        private TaskProvider $provider,
        private EntityManagerInterface$entityManager,
    ) {
    }

    public function create(string $name): Task
    {
        $task = $this->provider->createNewTask();
        $task->setName($name);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }
}