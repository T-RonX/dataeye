<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Locale\Entity\Timezone;
use App\User\Entity\User;
use App\UserPreference\Entity\UserPreferenceTimezone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class Load020_User extends Fixture
{
    private EntityManagerInterface $entityManager;

    public function load(ObjectManager|EntityManagerInterface $manager): void
    {
        $this->entityManager = $manager;

        $user_1 = new User();
        $user_1->setUsername('user1');
        $user_1->setPassword(password_hash('user1', PASSWORD_BCRYPT));
        $user_1->getPreferences()->add($this->createTimezonePreference($user_1, 'timezone_Europe/Amsterdam'));

        $this->addReference('user_1', $user_1);
        $manager->persist($user_1);

        $user_2 = new User();
        $user_2->setUsername('user2');
        $user_2->setPassword(password_hash('user2', PASSWORD_BCRYPT));
        $user_2->getPreferences()->add($this->createTimezonePreference($user_2, 'timezone_Europe/London'));

        $this->addReference('user_2', $user_2);
        $manager->persist($user_2);

        $user_3 = new User();
        $user_3->setUsername('user3');
        $user_3->setPassword(password_hash('user3', PASSWORD_BCRYPT));
        $user_3->getPreferences()->add($this->createTimezonePreference($user_3, 'timezone_America/Los_Angeles'));

        $this->addReference('user_3', $user_1);
        $manager->persist($user_3);

        $user_1->getAssociatedWith()->add($user_2);
        $user_1->getAssociatedTo()->add($user_2);
        $user_1->getAssociatedWith()->add($user_3);
        $user_1->getAssociatedTo()->add($user_3);
        $user_2->getAssociatedWith()->add($user_1);
        $user_2->getAssociatedTo()->add($user_1);

        $manager->flush();
    }

    private function createTimezonePreference(User $user, string $timezone): UserPreferenceTimezone
    {
        $preference = (new UserPreferenceTimezone())
            ->setUser($user)
            ->setTimezone($this->getReference($timezone, Timezone::class));

        $this->entityManager->persist($preference);

        return $preference;
    }
}
