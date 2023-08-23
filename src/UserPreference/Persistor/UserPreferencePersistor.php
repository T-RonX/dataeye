<?php

declare(strict_types=1);

namespace App\UserPreference\Persistor;

use App\User\Entity\User;
use App\UserPreference\Entity\UserPreference;
use App\UserPreference\Entity\UserPreferenceTimezone;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserPreferencePersistor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }


    public function save(User $user, array $preferences): void
    {
        $timezone = $preferences['timezone'];

        $preference = ($user->getPreferences()->findFirst(static fn (int $i, UserPreference $preference) => $preference instanceof UserPreferenceTimezone) ?? new UserPreferenceTimezone())
            ->setUser($user)
            ->setTimezone($timezone);

        $this->entityManager->persist($preference);
        $this->entityManager->flush();
    }
}
