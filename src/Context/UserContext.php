<?php

declare(strict_types=1);

namespace App\Context;

use App\User\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

readonly class UserContext
{
    public function __construct(
        private Security $security
    ) {
    }

    public function hasUser(): bool
    {
        return $this->security->getUser() !== null;
    }
    
    public function getUser(): User
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$this->hasUser())
        {
            throw new NoUserInContextException();
        }

        return $user;
    }
}