<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Error;

class ForbiddenError extends UserErrorWithCode
{
    public const ERROR_CODE = 'FORBIDDEN';

    public function __construct(string $message = 'Access denied', \Throwable $previous = null)
    {
        parent::__construct($message, self::ERROR_CODE, $previous);
    }
}
