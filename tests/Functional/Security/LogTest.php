<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Infrastructure\Test\Functional\Controller\ControllerTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class LogTest extends ControllerTestCase
{
    use ResetDatabase;
    use Factories;

    public function testLoginLogout()
    {
        // Login
        static::getKernelBrowser()->request(
            Request::METHOD_POST,
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            <<<JSON
{
    "username": "admin@example.com",
    "password": "password"
}
JSON
        );

        $response = static::getKernelBrowser()->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $cookies = $response->headers->getCookies();
        $this->assertNotEmpty($cookies);

        $jwtCookie = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'BEARER') {
                $jwtCookie = $cookie;
            }
        }

        $this->assertNotNull($jwtCookie);
        $this->assertMatchesRegularExpression("/^([a-zA-Z0-9_=]+)\.([a-zA-Z0-9_=]+)\.([a-zA-Z0-9_\-\+\/=]*)/", $jwtCookie->getValue());

        static::getKernelBrowser()->request(
            Request::METHOD_GET,
            '/logout'
        );

        // Logout
        $response = static::getKernelBrowser()->getResponse();

        $cookies = $response->headers->getCookies();
        $this->assertNotEmpty($cookies);

        $jwtCookie = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'BEARER') {
                $jwtCookie = $cookie;
            }
        }

        $this->assertNotNull($jwtCookie);
        $this->assertNull($jwtCookie->getValue());
    }
}
