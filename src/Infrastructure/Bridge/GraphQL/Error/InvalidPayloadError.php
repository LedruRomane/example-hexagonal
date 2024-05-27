<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Error;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidPayloadError extends UserErrorWithCode
{
    public const ERROR_CODE = 'INVALID_PAYLOAD';

    /** @var ConstraintViolationListInterface */
    private $violations;

    public function __construct(
        ConstraintViolationListInterface $violations,
        string $message = 'Invalid payload',
        \Throwable $previous = null
    ) {
        parent::__construct($message, self::ERROR_CODE, $previous);

        $this->violations = $violations;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
