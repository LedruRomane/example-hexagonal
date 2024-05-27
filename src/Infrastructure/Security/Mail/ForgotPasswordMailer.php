<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Mail;

use App\Domain\User\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

/**
 * Sends an email to the user with a link to reset his password.
 */
class ForgotPasswordMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
    ) {
    }

    public function send(User $user, ResetPasswordToken $token): void
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Votre demande de réinitialisation de mot de passe')
            ->htmlTemplate('user/security/forgot_password.email.html.twig')
            ->context([
                'resetToken' => $token,
            ])
        ;

        $this->mailer->send($email);
    }
}
