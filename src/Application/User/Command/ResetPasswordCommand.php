<?php

declare(strict_types=1);

namespace App\Application\User\Command;

use App\Application\User\Payload\ResetPasswordPayload;
use Symfony\Component\Validator\Constraints as Assert;

final class ResetPasswordCommand
{
    public function __construct(
        public string $token,
        #[Assert\Valid]
        public ResetPasswordPayload $payload,
    ) {
    }
}
