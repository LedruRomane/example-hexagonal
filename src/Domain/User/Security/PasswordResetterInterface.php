<?php

declare(strict_types=1);

namespace App\Domain\User\Security;

use App\Domain\User\User;

interface PasswordResetterInterface
{
    /**
     * Generates a new token for resetting the user password & send the email with the reset link.
     */
    public function sendResetPasswordMail(User $user): void;

    /**
     * Validates the token and reset the user password if it's valid.
     */
    public function resetPassword(string $token, string $newPassword): void;
}
