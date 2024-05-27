<?php

declare(strict_types=1);

namespace App\Infrastructure\Test\Listener;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;

/**
 * Enables debug mode in kernel for test cases marked with "@group debug",
 * allowing to access debug mode specific services (e.g: accessing the profiler & collectors)
 */
class SymfonyDebugModeListener implements TestListener
{
    use TestListenerDefaultImplementation;

    private static ?string $previous;

    public function startTest(Test $test): void
    {
        if (!$test instanceof TestCase) {
            return;
        }

        self::$previous = $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'];

        if (\in_array('debug', $test->getGroups(), true)) {
            $_ENV['APP_DEBUG'] = $_SERVER['APP_DEBUG'] = '1';
        }
    }

    public function endTest(Test $test, float $time): void
    {
        if (!$test instanceof TestCase) {
            return;
        }

        // Debug mode is restored after each test case
        $_ENV['APP_DEBUG'] = $_SERVER['APP_DEBUG'] = self::$previous;
        self::$previous = null;
    }
}
