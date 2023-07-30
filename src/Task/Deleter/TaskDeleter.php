<?php

declare(strict_types=1);

namespace App\Task\Deleter;

use App\Task\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

readonly class TaskDeleter
{
    public function __construct(
        private EntityManagerInterface$entityManager,
    ) {
    }
    public function delete(Task $task): Task
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return $task;
    }
}