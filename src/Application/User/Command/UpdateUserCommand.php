<?php

declare(strict_types=1);

namespace App\Application\User\Command;

use App\Application\User\Payload\UserPayload;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateUserCommand
{
    public function __construct(
        public Ulid $userUid,
        #[Assert\Valid]
        public UserPayload $payload
    ) {
    }
}
