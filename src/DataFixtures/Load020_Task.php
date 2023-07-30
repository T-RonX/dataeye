<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Task\Entity\Task;
use App\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class Load020_Task extends Fixture
{
    public function load(ObjectManager|EntityManagerInterface $manager): void
    {
        $task = new Task();
        $task->setName('Task 1');
        $task->setCreatedBy($this->getReference('user', User::class));

        $manager->persist($task);
        $manager->flush();
    }
}
