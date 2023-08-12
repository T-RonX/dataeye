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
        $user_1 = new User();
        $user_1->setUsername('user1');
        $user_1->setPassword(password_hash('user1', PASSWORD_BCRYPT));

        $this->addReference('user_1', $user_1);

        $user_2 = new User();
        $user_2->setUsername('user2');
        $user_2->setPassword(password_hash('user2', PASSWORD_BCRYPT));

        $this->addReference('user_2', $user_2);

        $user_1->getAssociatedWith()->add($user_2);
        $user_1->getAssociatedTo()->add($user_2);
        $user_2->getAssociatedWith()->add($user_1);
        $user_2->getAssociatedTo()->add($user_1);

        $manager->persist($user_1);
        $manager->persist($user_2);

        $manager->flush();
    }
}
