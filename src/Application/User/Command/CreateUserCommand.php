<?php

declare(strict_types=1);

namespace App\Application\User\Command;

use App\Application\User\Payload\UserPayload;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateUserCommand
{
    public function __construct(
        #[Assert\Valid]
        public UserPayload $payload
    ) {
    }
}
