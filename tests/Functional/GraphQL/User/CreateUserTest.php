<?php

declare(strict_types=1);

namespace App\Tests\Functional\GraphQL\User;

use App\Infrastructure\Fixtures\Factory\UserFactory;
use App\Infrastructure\Test\Functional\Controller\GraphQLTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CreateUserTest extends GraphQLTestCase
{
    use ResetDatabase;
    use Factories;

    public function testUnauthenticated(): void
    {
        $this->executeGraphQL(['payload' => []], $this->getInputContent('testCreateUser'));

        $this->assertGraphQLAccessDenied();
    }

    public function testAuthenticatedAsUser(): void
    {
        $this->loginAsUser();

        $this->executeGraphQL(['payload' => []], $this->getInputContent('testCreateUser'));

        $this->assertGraphQLAccessDenied();
    }

    /**
     * @dataProvider provide testValid
     */
    public function testValid(array $payload): void
    {
        $this->loginAsAdmin();

        $this->executeGraphQL(compact('payload'), $this->getInputContent('testCreateUser'));

        $this->assertValidGraphQLResponse();
        $this->assertJsonResponseMatchesExpectations();
    }

    public function provide testValid(): iterable
    {
        yield 'every fields, admin' => [[
            'email' => 'john.doe@example.com',
            'password' => 'p@55w04d-cliopioxb0000n322j023u1wd',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'active' => true,
            'admin' => true,
        ]];

        yield 'every fields, user' => [[
            'email' => 'jane.doe@example.com',
            'password' => 'p@55w04d-cliopioxf0001n322rny5r5nt',
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'active' => true,
            'admin' => false,
        ]];
    }

    /**
     * @dataProvider provide testInvalid
     */
    public function testInvalid(array $payload): void
    {
        $this->loginAsAdmin();

        UserFactory::new()->createOne([
            'email' => 'test-existing-user@example.com',
        ]);

        $this->executeGraphQL([
            'payload' => $payload,
        ], $this->getInputContent('testCreateUser'));

        $this->assertGraphQLInvalidPayloadResponse();
        $this->assertJsonResponseMatchesExpectations();
    }

    public function provide testInvalid(): iterable
    {
        yield 'empty fields' => [[
            'email' => null,
            'password' => null,
            'firstname' => null,
            'lastname' => null,
            'active' => false,
            'admin' => true,
        ]];

        yield 'existing email' => [[
            'email' => 'test-existing-user@example.com',
            'password' => 'p@55w04d-cliopioxb0000n322j023u1wd',
            'firstname' => 'we-dont-care',
            'lastname' => 'we-dont-care',
            'admin' => true,
        ]];

        $o = str_repeat('o', 256);
        $wayTooLongValue = "way-too-lo{$o}ng-value";

        yield 'too long' => [[
            'email' => $wayTooLongValue,
            'password' => 'p@55w04d-cliopioxf0001n322rny5r5nt',
            'firstname' => $wayTooLongValue,
            'lastname' => $wayTooLongValue,
            'admin' => false,
        ]];
    }
}
