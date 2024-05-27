<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\Symfony\DependencyInjection;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * To use conjointly with {$@see ServiceSubscriberInterface}
 */
trait ServiceLocatorTrait
{
    private ?ContainerInterface $container = null;

    /**
     * This makes symfony inject the service locator described by {@link ServiceSubscriberInterface::getSubscribedServices()}
     * (It's not the Symfony DI container instance)
     *
     * @required
     */
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $previous = $this->container;
        $this->container = $container;

        return $previous;
    }

    /**
     * @template T
     *
     * @param class-string<T> $serviceName
     *
     * @return T
     *
     * @phpstan-return T
     */
    protected function get(string $serviceName)
    {
        \assert($this->container !== null);

        return $this->container->get($serviceName);
    }
}
