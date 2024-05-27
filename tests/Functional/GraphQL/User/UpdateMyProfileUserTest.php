<?php

declare(strict_types=1);

namespace App\Tests\Functional\GraphQL\User;

use App\Infrastructure\Fixtures\Factory\UserFactory;
use App\Infrastructure\Test\Functional\Controller\GraphQLTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UpdateMyProfileUserTest extends GraphQLTestCase
{
    use ResetDatabase;
    use Factories;

    public function testUnauthenticated(): void
    {
        $this->executeGraphQL(['payload' => []], $this->getInputContent('testUpdateMyProfileUser'));

        $this->assertGraphQLAccessDenied();
    }

    /**
     * @dataProvider provide testValid
     */
    public function testValid(array $payload): void
    {
        $this->loginAsUser();

        $this->executeGraphQL(compact('payload'), $this->getInputContent('testUpdateMyProfileUser'));

        $this->assertValidGraphQLResponse();
        $this->assertJsonResponseMatchesExpectations();
    }

    public function provide testValid(): iterable
    {
        yield 'every fields, as myself' => [[
            'email' => UserFactory::EMAIL_USER,
            'firstname' => 'User',
            'lastname' => 'Myself',
        ]];
    }

    /**
     * @dataProvider provide testInvalid
     */
    public function testInvalid(array $payload): void
    {
        $this->loginAsUser();

        $this->executeGraphQL(compact('payload'), $this->getInputContent('testUpdateMyProfileUser'));

        $this->assertGraphQLInvalidPayloadResponse();
        $this->assertJsonResponseMatchesExpectations();
    }

    public function provide testInvalid(): iterable
    {
        yield 'empty fields' => [[
            'email' => null,
            'firstname' => null,
            'lastname' => null,
        ]];

        $o = str_repeat('o', 256);
        $wayTooLongValue = "way-too-lo{$o}ng-value";

        yield 'too long' => [[
            'email' => $wayTooLongValue,
            'firstname' => $wayTooLongValue,
            'lastname' => $wayTooLongValue,
        ]];
    }
}
