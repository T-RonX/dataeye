<?php

declare(strict_types=1);

namespace App\Task\Updater;

use App\Task\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

readonly class TaskUpdater
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }
    public function update(Task $task, string $name): Task
    {
        $task->setName($name);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }
}