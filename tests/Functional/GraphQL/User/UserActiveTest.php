<?php

declare(strict_types=1);

namespace App\Tests\Functional\GraphQL\User;

use App\Infrastructure\Fixtures\Factory\UserFactory;
use App\Infrastructure\Test\Functional\Controller\GraphQLTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserActiveTest extends GraphQLTestCase
{
    use ResetDatabase;
    use Factories;

    public function testLoginForInactiveUser(): void
    {
        $this->loginAs(UserFactory::EMAIL_INACTIVE_USER, null, true);
    }

    /**
     * @dataProvider provide testTurnInactive
     */
    public function testApiForInactiveUser(string $uid, array $payload): void
    {
        // Login as admin then turn itself inactive
        $this->loginAsAdmin();
        $this->executeGraphQL(compact('uid', 'payload'), $this->getInputContent('testTurnUserInactive'));
        $this->assertValidGraphQLResponse();

        // Assert api access denied
        $this->executeGraphQL([], $this->getInputContent('testQueryInactiveUser'))->getContent();
        $this->assertJsonResponseMatchesExpectations();
    }

    public function provide testTurnInactive(): iterable
    {
        yield 'active to inactive' => [
            UserFactory::ULID_ADMIN,
            [
                'email' => UserFactory::EMAIL_ADMIN,
                'password' => 'p@55w04d-cliopioxb0000n322j023u1wd',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'active' => false,
            ],
        ];
    }
}
