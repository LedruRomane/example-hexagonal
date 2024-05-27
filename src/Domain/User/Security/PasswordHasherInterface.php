<?php

declare(strict_types=1);

namespace App\Domain\User\Security;

interface PasswordHasherInterface
{
    public function hash(#[\SensitiveParameter] string $plainPassword): string;
}
