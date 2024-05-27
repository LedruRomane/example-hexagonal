<?php

declare(strict_types=1);

namespace App\Infrastructure\Test\Functional;

/**
 * This trait defines common contract for any trait/class requiring to call a service from the container.
 *
 * Such usages are:
 * - accessing Doctrine
 * - accessing a mock service to alter its data
 * - accessing a service collecting data during a request in order to inspect it in tests
 * - ...
 */
trait DITrait
{
    /**
     * @template T of object|null
     *
     * @param class-string<T> $service
     *
     * @phpstan-return T
     */
    abstract protected static function getService(string $service): ?object;
}
