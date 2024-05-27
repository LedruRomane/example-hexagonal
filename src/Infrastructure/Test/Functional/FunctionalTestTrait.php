<?php

declare(strict_types=1);

namespace App\Infrastructure\Test\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

trait FunctionalTestTrait
{
    use DITrait;
    use JsonAssertionsTrait;

    /**
     * @before
     *
     * @internal
     */
    protected function functionalSetUp(): void
    {
        static::bootKernel();
    }

    /**
     * @after
     *
     * @internal
     */
    public function functionalTearDown(): void
    {
        if (is_a(static::class, KernelTestCase::class, true)) {
            static::ensureKernelShutdown();
        }
    }

    protected static function bootKernel(array $options = []): KernelInterface
    {
        if (!is_a(static::class, KernelTestCase::class, true)) {
            throw new \LogicException(sprintf(
                'You must override the "%s" method or extend "%s".', __METHOD__,
                KernelTestCase::class
            ));
        }

        return parent::bootKernel($options);
    }

    protected static function getKernel(): KernelInterface
    {
        if (!is_a(static::class, KernelTestCase::class, true)) {
            throw new \LogicException(sprintf(
                'You must override the "%s" method or extend "%s".', __METHOD__,
                KernelTestCase::class
            ));
        }

        return static::$kernel;
    }

    /**
     * Implements {@link DITrait}
     */
    protected static function getService(string $service): ?object
    {
        if (!is_a(static::class, KernelTestCase::class, true)) {
            return static::getKernel()->getContainer()->get($service);
        }

        return static::getContainer()->get($service);
    }
}
