<?php

declare(strict_types=1);

namespace App\Domain\User;

use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

/**
 * @see https://github.com/SymfonyCasts/reset-password-bundle/blob/main/docs/manual-setup.md
 */
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    private ?int $id = null;

    private User $user;

    public function __construct(User $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->initialize($expiresAt, $selector, $hashedToken);

        $this->user = $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @internal For testing purposes only
     */
    public function expire(): void
    {
        $this->expiresAt = new \DateTimeImmutable('-1 day');
    }
}
