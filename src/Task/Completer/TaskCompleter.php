<?php

declare(strict_types=1);

namespace App\Task\Completer;

use App\Task\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

readonly class TaskCompleter
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function complete(Task $task): void
    {
    }
}