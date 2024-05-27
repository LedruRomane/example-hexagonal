<?php

declare(strict_types=1);

namespace App\Tests\Functional\GraphQL\User;

use App\Infrastructure\Fixtures\Factory\UserFactory;
use App\Infrastructure\Test\Functional\Controller\GraphQLTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UpdateUserTest extends GraphQLTestCase
{
    use ResetDatabase;
    use Factories;

    public function testUnauthenticated(): void
    {
        $this->executeGraphQL(['uid' => (string) new Ulid(), 'payload' => []], $this->getInputContent('testUpdateUser'));

        $this->assertGraphQLAccessDenied();
    }

    public function testAuthenticatedAsUser(): void
    {
        $this->loginAsUser();

        $this->executeGraphQL(['uid' => (string) new Ulid(), 'payload' => []], $this->getInputContent('testUpdateUser'));

        $this->assertGraphQLAccessDenied();
    }

    /**
     * @dataProvider provide testValid
     */
    public function testValid(string $uid, array $payload): void
    {
        $this->loginAsAdmin();

        $this->executeGraphQL(compact('uid', 'payload'), $this->getInputContent('testUpdateUser'));

        $this->assertValidGraphQLResponse();
        $this->assertJsonResponseMatchesExpectations();
    }

    public function provide testValid(): iterable
    {
        yield 'every fields, admin' => [
            UserFactory::ULID_ADMIN,
            [
                'email' => UserFactory::EMAIL_ADMIN,
                'password' => 'p@55w04d-cliopioxb0000n322j023u1wd',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'admin' => true,
            ],
        ];

        yield 'every fields, user' => [
            UserFactory::ULID_USER,
            [
                'email' => UserFactory::EMAIL_USER,
                'password' => 'p@55w04d-cliopioxf0001n322rny5r5nt',
                'firstname' => 'Jane',
                'lastname' => 'Doe',
                'admin' => false,
            ],
        ];

        yield 'user to admin' => [
            UserFactory::ULID_USER,
            [
                'email' => UserFactory::EMAIL_USER,
                'password' => 'p@55w04d-cliopioxf0001n322rny5r5nt',
                'firstname' => 'Jane',
                'lastname' => 'Doe',
                'admin' => true,
            ],
        ];

        yield 'admin to user' => [
            UserFactory::ULID_ADMIN,
            [
                'email' => UserFactory::EMAIL_ADMIN,
                'password' => 'p@55w04d-cliopioxb0000n322j023u1wd',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'admin' => false,
            ],
        ];
    }

    /**
     * @dataProvider provide testInvalid
     */
    public function testInvalid(string $uid, array $payload): void
    {
        $this->loginAsAdmin();

        UserFactory::new()->createOne([
            'email' => 'test-existing-user@example.com',
        ]);

        $this->executeGraphQL(compact('uid', 'payload'), $this->getInputContent('testUpdateUser'));

        $this->assertGraphQLInvalidPayloadResponse();
        $this->assertJsonResponseMatchesExpectations();
    }

    public function provide testInvalid(): iterable
    {
        yield 'empty fields' => [
            UserFactory::ULID_USER,
            [
                'email' => null,
                'password' => null,
                'firstname' => null,
                'lastname' => null,
                'admin' => null,
            ],
        ];

        yield 'existing email' => [
            UserFactory::ULID_USER,
            [
                'email' => 'test-existing-user@example.com',
                'password' => 'p@55w04d-cliopioxb0000n322j023u1wd',
                'firstname' => 'we-dont-care',
                'lastname' => 'we-dont-care',
                'admin' => true,
            ],
        ];

        $o = str_repeat('o', 256);
        $wayTooLongValue = "way-too-lo{$o}ng-value";

        yield 'too long' => [
            UserFactory::ULID_USER,
            [
                'email' => $wayTooLongValue,
                'password' => 'p@55w04d-cliopioxf0001n322rny5r5nt',
                'firstname' => $wayTooLongValue,
                'lastname' => $wayTooLongValue,
                'admin' => false,
            ],
        ];
    }
}
