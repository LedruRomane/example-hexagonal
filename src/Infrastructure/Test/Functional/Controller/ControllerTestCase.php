<?php

declare(strict_types=1);

namespace App\Infrastructure\Test\Functional\Controller;

use App\Infrastructure\Test\Functional\FunctionalTestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ControllerTestCase extends WebTestCase
{
    use FunctionalTestTrait;

    private static ?KernelBrowser $kernelBrowser = null;

    public function tearDown(): void
    {
        parent::tearDown();
        self::$kernelBrowser = null;
    }

    protected static function getKernelBrowser(): KernelBrowser
    {
        if (self::$kernelBrowser !== null) {
            return self::$kernelBrowser;
        }

        // Fetching the test client ourselves, which does not require to shutdown the kernel before, nor between tests
        // allowing mocked services mutations as well as improving perfs.
        /* @var KernelBrowser $kernelBrowser */
        $kernelBrowser = self::getService('test.client');
        \assert($kernelBrowser instanceof KernelBrowser);
        self::$kernelBrowser = $kernelBrowser;
        self::$kernelBrowser->disableReboot();

        // Configure the client for web test cases assertions trait:
        \Closure::bind(static function () use ($kernelBrowser): void {
            WebTestCase::getClient($kernelBrowser);
        }, null, WebTestCase::class)();

        return self::$kernelBrowser;
    }

    protected function getClientResponse(): Response
    {
        return self::getKernelBrowser()->getResponse();
    }
}
