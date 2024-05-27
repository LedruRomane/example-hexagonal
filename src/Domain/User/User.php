<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Common\Behavior\TimestampableTrait;
use App\Domain\Common\Behavior\UlidTrait;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class User
{
    use UlidTrait;
    use TimestampableTrait;

    private string $email;
    private string $password;

    private string $firstname;
    private string $lastname;

    private bool $active;
    private bool $admin;

    public function __construct(
        string $email,
        string $password,
        string $firstname,
        string $lastname,
        bool $active = true,
        bool $admin = false,
    ) {
        $this->initIdentity();

        $this->email = $email;
        $this->password = $password;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->active = $active;
        $this->admin = $admin;
    }

    /**
     * As a admin, update a user profile.
     */
    public function update(
        string $email,
        string $firstname,
        string $lastname,
        bool $active,
        bool $admin,
    ): void {
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->active = $active;
        $this->admin = $admin;
    }

    /**
     * As a user, update his own profile.
     */
    public function updateProfile(
        string $email,
        string $firstname,
        string $lastname,
    ): void {
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }

    public function changePassword(string $newPassword): void
    {
        $this->password = $newPassword;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isAdmin(): bool
    {
        return $this->admin;
    }
}
