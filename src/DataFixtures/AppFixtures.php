<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Task\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager|EntityManagerInterface $manager): void
    {
        $manager->beginTransaction();

        $task = new Task();
        $task->setName('Task 1');
        $manager->persist($task);

        $manager->flush();
    }
}
