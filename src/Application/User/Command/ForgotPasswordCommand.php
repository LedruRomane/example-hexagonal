<?php

declare(strict_types=1);

namespace App\Application\User\Command;

/**
 * Whenever a user forgets his password, he can request a reset link through this command,
 * by providing his email address.
 */
final class ForgotPasswordCommand
{
    public function __construct(
        public readonly string $email
    ) {
    }
}
