<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Resolver;

use App\Domain\Common\Exception\NotFoundException;
use App\Infrastructure\Bridge\GraphQL\Error\NotFoundError;
use App\Infrastructure\Bridge\GraphQL\SecurityTrait;
use App\Infrastructure\Bridge\Symfony\DependencyInjection\ServiceLocatorTrait;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractResolver implements QueryInterface, ServiceSubscriberInterface
{
    use ServiceLocatorTrait;
    use SecurityTrait;

    /**
     * Handle common GraphQL errors conversion.
     *
     * @template T
     *
     * @phpstan-param callable(): T $callable
     *
     * @return T
     */
    public function withGraphQLErrorHandler(callable $callable): mixed
    {
        try {
            return $callable();
        } catch (NotFoundException $ex) {
            throw new NotFoundError($ex->getMessage());
        }
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->get(LoggerInterface::class);
    }

    protected function getSecurity(): Security
    {
        return $this->get(Security::class);
    }

    public static function getSubscribedServices(): array
    {
        return [
            Security::class,
            TranslatorInterface::class,
            LoggerInterface::class,
        ];
    }
}
