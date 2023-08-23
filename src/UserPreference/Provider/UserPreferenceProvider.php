<?php

declare(strict_types=1);

namespace App\UserPreference\Provider;

use App\User\Entity\User;
use App\UserPreference\Entity\UserPreferenceTimezone;
use App\UserPreference\Repository\UserPreferenceRepository;

readonly class UserPreferenceProvider
{
    public function __construct(
        private readonly UserPreferenceRepository $repository,
    ) {
    }

    public function getTimezone(User $user): UserPreferenceTimezone
    {
        return $this->repository->getTimezone($user);
    }
}
