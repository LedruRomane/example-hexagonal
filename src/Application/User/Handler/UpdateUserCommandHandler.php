<?php

declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\User\Command\UpdateUserCommand;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Security\PasswordHasherInterface;
use App\Domain\User\User;

class UpdateUserCommandHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher
    ) {
    }

    public function __invoke(UpdateUserCommand $command): User
    {
        $user = $this->userRepository->getOneByUid($command->userUid);

        $payload = $command->payload;

        $user->update(
            $payload->email,
            $payload->firstname,
            $payload->lastname,
            $payload->active,
            $payload->admin,
        );

        // Only change the password if a new one was provided:
        if ($payload->password !== null) {
            $user->changePassword($this->passwordHasher->hash($payload->password));
        }

        return $user;
    }
}
