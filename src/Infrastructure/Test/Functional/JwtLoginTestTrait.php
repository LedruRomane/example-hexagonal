<?php

declare(strict_types=1);

namespace App\Infrastructure\Test\Functional;

use App\Infrastructure\Fixtures\Factory\UserFactory;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait JwtLoginTestTrait
{
    abstract protected static function getKernelBrowser(): KernelBrowser;

    /**
     * Login by making a http call and assert it succeeded or failed.
     * + save the token to be used on next calls with the kernel browser on success.
     */
    protected function loginAs(string $username, string $password = null, bool $assertFails = false): void
    {
        $jwtToken = $this->login($username, $password, $assertFails);

        static::getKernelBrowser()->setServerParameters([
            'HTTP_AUTHORIZATION' => "Bearer $jwtToken",
        ]);
    }

    /**
     * Login as the base admin user.
     */
    protected function loginAsAdmin(): void
    {
        $this->loginAs(UserFactory::EMAIL_ADMIN);
    }

    /**
     * Login as the base user.
     */
    protected function loginAsUser(): void
    {
        $this->loginAs(UserFactory::EMAIL_USER);
    }

    /**
     * Login by making a http call and assert it succeeded or failed.
     * Returns the token on success.
     */
    private function login(
        string $username,
        string $password = null,
        bool $assertFails = false,
        bool $useCache = true
    ): ?string {
        static $cachedLogin = [];
        $password ??= 'password';
        $cacheKey = hash('crc32b', $username . $password);

        if ($useCache && $token = $cachedLogin[$cacheKey] ?? false) {
            return $token;
        }

        static::getKernelBrowser()->request(
            Request::METHOD_POST,
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            <<<JSON
{
    "username": "$username",
    "password": "$password"
}
JSON
        );

        $response = static::getKernelBrowser()->getResponse();

        if ($assertFails) {
            Assert::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), 'Login should fail.');

            return null;
        }

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), 'Login should be successful.');

        /** @var \stdClass $content */
        $content = json_decode($response->getContent() ?: '', false, 512, JSON_THROW_ON_ERROR);

        $token = $content->token;

        $useCache && $cachedLogin[$cacheKey] = $token;

        return $token;
    }
}
