<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\User\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class Load010_User extends Fixture
{
    public function load(ObjectManager|EntityManagerInterface $manager): void
    {
        $manager->beginTransaction();

        $task = new User();
        $task->setUsername('ron');
        $task->setPassword(password_hash('ron',  PASSWORD_BCRYPT));
        $manager->persist($task);

        $manager->flush();
    }
}
