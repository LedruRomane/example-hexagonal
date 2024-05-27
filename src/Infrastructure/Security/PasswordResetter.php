<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\User\Security\PasswordHasherInterface;
use App\Domain\User\Security\PasswordResetterInterface;
use App\Domain\User\User;
use App\Infrastructure\Security\Mail\ForgotPasswordMailer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class PasswordResetter implements PasswordResetterInterface
{
    public function __construct(
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly ForgotPasswordMailer $mailer,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }

    public function sendResetPasswordMail(User $user): void
    {
        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $ex) {
            $this->logger->info('There was a problem handling the password reset request for user {user} - {reason}', [
                'exception' => $ex,
                'exception_message' => $ex->getMessage(),
                'message_problem' => ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE,
                'reason' => $ex->getReason(),
            ]);

            return;
        }

        $this->mailer->send($user, $resetToken);
    }

    /**
     * @throws ResetPasswordExceptionInterface on invalid or expired token while trying to fetch the user
     */
    public function resetPassword(string $token, string $newPassword): void
    {
        // Will throw an exception if the token is invalid.
        $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);

        if (!$user instanceof User) {
            throw new \LogicException('The user is not an instance of User');
        }

        $this->resetPasswordHelper->removeResetRequest($token);

        // Encode the plain password, and set it.
        $user->changePassword($this->passwordHasher->hash($newPassword));
    }
}
