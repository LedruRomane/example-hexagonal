<?php

declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\User\Command\UpdateMyProfileCommand;
use App\Domain\User\User;

class UpdateMyProfileCommandHandler
{
    public function __construct(
    ) {
    }

    public function __invoke(UpdateMyProfileCommand $command): User
    {
        $payload = $command->payload;
        $user = $command->user;

        $user->updateProfile(
            $payload->email,
            $payload->firstname,
            $payload->lastname,
        );

        return $user;
    }
}
