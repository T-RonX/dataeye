<?php

declare(strict_types=1);

namespace App\User\Provider;

use App\User\Entity\User;
use App\User\Repository\UserRepository;

class UserProvider
{
    public function __construct(
        private readonly UserRepository $repository,
    ) {
    }

    public function userExists(string $username): bool
    {
        return $this->repository->usernameExists($username);
    }

    public function createNewUser(): User
    {
        return new User();
    }

    public function getUser(int $userId): ?User
    {
        return $this->repository->find($userId);
    }
}
