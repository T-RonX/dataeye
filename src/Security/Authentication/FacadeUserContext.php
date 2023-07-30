<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\Exception\ItemNotFoundException;
use App\Facade\BaseFacadeContext;
use App\User\Entity\User;
use App\User\Provider\UserProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class FacadeUserContext extends BaseFacadeContext
{
    public function __construct(
        private UserProvider $userProvider,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function getOptionName(): string
    {
        return 'user';
    }

    public function setContext(mixed $value): void
    {
        $userId = (int) $value;
        $user = $this->userProvider->getUser($userId);

        if ($user === null)
        {
            throw new ItemNotFoundException(User::class, $value);
        }

        $token = new IdToken($user->getRoles());
        $token->setUser($user);

        $this->tokenStorage->setToken($token);
    }
}