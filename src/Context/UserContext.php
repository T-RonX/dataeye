<?php

declare(strict_types=1);

namespace App\Context;

use App\Locale\Entity\Timezone;
use App\User\Entity\User;
use App\UserPreference\Provider\UserPreferenceProvider;
use Symfony\Bundle\SecurityBundle\Security;

readonly class UserContext
{
    public function __construct(
        private Security $security,
        private UserPreferenceProvider $preferenceProvider,
    ) {
    }

    public function hasUser(): bool
    {
        return $this->security->getUser() !== null;
    }

    public function getUser(): User
    {
        $this->validateUser();

        /** @var User $user */
        $user = $this->security->getUser();

        return $user;
    }

    private function validateUser(): void
    {
        if (!$this->security->getUser())
        {
            throw new NoUserInContextException();
        }
    }

    public function getTimezone(): Timezone
    {
        return $this->preferenceProvider->getTimezone($this->getUser())->getTimezone();
    }
}