<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Locale\Entity\Timezone;
use DateTimeZone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class Load010_Locale extends Fixture
{
    private EntityManagerInterface $entityManager;

    public function load(ObjectManager|EntityManagerInterface $manager): void
    {
        $this->entityManager = $manager;

        foreach (DateTimeZone::listIdentifiers() as $name)
        {
            $timezone = $this->createTimeZone(timezone_version_get(), $name);
            $this->addReference('timezone_'.$name, $timezone);

            $this->entityManager->persist($timezone);
        }

        $manager->flush();
    }

    private function createTimeZone(string $version, string $name): Timezone
    {
        return (new Timezone())
            ->setName($name)
            ->setVersion($version);
    }
}
