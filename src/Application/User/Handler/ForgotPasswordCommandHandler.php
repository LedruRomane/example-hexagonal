<?php

declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\User\Command\ForgotPasswordCommand;
use App\Domain\Common\Exception\NotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Security\PasswordResetterInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ForgotPasswordCommandHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordResetterInterface $passwordResetter,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }

    public function __invoke(ForgotPasswordCommand $command): void
    {
        try {
            $user = $this->userRepository->getOneByEmail($command->email);

            $this->passwordResetter->sendResetPasswordMail($user);
        } catch (NotFoundException $ex) {
            // On no user found for given email, avoid to leak it exists or not, but just send a successful response.
            $this->logger->info('Email {email} not found, ignored the sending forgot password email without error to avoid leaking the user existence', [
                'exception' => $ex,
                'exception_message' => $ex->getMessage(),
                'email' => $command->email,
            ]);
        }
    }
}
