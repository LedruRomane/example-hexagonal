<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\ExceptionConverter;

use App\Infrastructure\Bridge\GraphQL\Error\AccessDeniedError;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Error\ExceptionConverterInterface;
use Overblog\GraphQLBundle\Error\UserWarning;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

/**
 * Adds our own logic to convert specific exceptions.
 *
 * @see https://github.com/overblog/GraphQLBundle/blob/master/docs/error-handling/index.md
 */
#[AsDecorator(decorates: ExceptionConverterInterface::class)]
class ExceptionConverter implements ExceptionConverterInterface
{
    public function __construct(
        #[AutowireDecorated]
        private readonly ExceptionConverterInterface $inner
    ) {
    }

    public function convertException(\Throwable $exception): \Throwable
    {
        // There is no better way currently to check if the error is an access denied error
        if (
            ($exception instanceof UserWarning || $exception instanceof UserError) &&
            $exception->getMessage() === 'Access denied to this field.'
        ) {
            return new AccessDeniedError($exception->getMessage(), $exception);
        }

        return $this->inner->convertException($exception);
    }
}
