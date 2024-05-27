<?php

declare(strict_types=1);

namespace App\Infrastructure\Test\Functional;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

trait DoctrineTrait
{
    use DITrait;

    public static function getManagerRegistry(): ManagerRegistry
    {
        // @phpstan-ignore-next-line
        return static::getService(ManagerRegistry::class);
    }

    /**
     * @param class-string $class
     */
    public static function getManager(string $class = null): ObjectManager
    {
        // @phpstan-ignore-next-line
        return $class
            ? static::getManagerRegistry()->getManagerForClass($class)
            : static::getManagerRegistry()->getManager()
        ;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return ObjectRepository<T>
     */
    public static function getRepository(string $class): ObjectRepository
    {
        return static::getManager()->getRepository($class);
    }
}
