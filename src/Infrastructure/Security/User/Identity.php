<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\User;

use App\Domain\User\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Represents a user identity which makes the link between Symfony's security system and our model.
 */
class Identity implements UserInterface, PasswordAuthenticatedUserInterface
{
    private const ROLE_ADMIN = 'ROLE_ADMIN';
    private const ROLE_USER = 'ROLE_USER';

    public function __construct(private readonly User $user)
    {
    }

    public static function roles(bool $admin): array
    {
        return $admin ? [self::ROLE_ADMIN] : [self::ROLE_USER];
    }

    public function getRoles(): array
    {
        return self::roles($this->user->isAdmin());
    }

    public function eraseCredentials(): void
    {
        // Noop
    }

    public function getUserIdentifier(): string
    {
        return $this->user->getEmail();
    }

    public function getPassword(): ?string
    {
        return $this->user->getPassword();
    }

    public function setPassword(string $newHashedPassword): void
    {
        $this->user->changePassword($newHashedPassword);
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
