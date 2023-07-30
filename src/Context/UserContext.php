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
    
    public function getUser(): User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User)
        {
            throw new NoUserInContextException();
        }

        return $user;
    }
}