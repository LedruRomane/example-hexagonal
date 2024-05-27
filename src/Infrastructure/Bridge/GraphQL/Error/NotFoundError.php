<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Error;

class NotFoundError extends UserErrorWithCode
{
    public const ERROR_CODE = 'NOT_FOUND';

    public function __construct(string $message = 'Not found', \Throwable $previous = null)
    {
        parent::__construct($message, self::ERROR_CODE, $previous);
    }
}
