<?php

declare(strict_types=1);

namespace App\Application\User\Command;

use App\Application\User\Payload\MyProfilePayload;
use App\Domain\User\User;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateMyProfileCommand
{
    public function __construct(
        public User $user,
        #[Assert\Valid]
        public MyProfilePayload $payload
    ) {
    }
}
