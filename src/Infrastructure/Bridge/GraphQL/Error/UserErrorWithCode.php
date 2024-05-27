<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Error;

use GraphQL\Error\UserError;

class UserErrorWithCode extends UserError implements HasErrorCode
{
    public function __construct(
        string $message,
        private readonly string $errorCode,
        \Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
