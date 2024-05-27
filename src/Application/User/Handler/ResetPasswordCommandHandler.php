<?php

declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\User\Command\ResetPasswordCommand;
use App\Domain\User\Security\PasswordResetterInterface;

class ResetPasswordCommandHandler
{
    public function __construct(
        private readonly PasswordResetterInterface $passwordResetter,
    ) {
    }

    public function __invoke(ResetPasswordCommand $command): void
    {
        $token = $command->token;

        $this->passwordResetter->resetPassword($token, $command->payload->newPassword);
    }
}
