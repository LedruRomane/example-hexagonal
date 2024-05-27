<?php

declare(strict_types=1);

namespace App\Tests\Functional\GraphQL\User;

use App\Infrastructure\Fixtures\Factory\UserFactory;
use App\Infrastructure\Test\Functional\Controller\GraphQLTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class FindUserTest extends GraphQLTestCase
{
    use ResetDatabase;
    use Factories;

    public function testUnauthenticated(): void
    {
        $this->executeGraphQL([
            'uid' => UserFactory::ULID_USER,
        ], $this->getInputContent('testFindUser'));

        $this->assertGraphQLAccessDenied();
    }

    public function testInvalidUser(): void
    {
        $this->loginAsUser();

        $this->executeGraphQL([
            'uid' => UserFactory::ULID_USER,
        ], $this->getInputContent('testFindUser'));

        $this->assertGraphQLAccessDenied();
    }

    public function testFound(): void
    {
        $this->loginAsAdmin();

        $this->executeGraphQL([
            'uid' => UserFactory::ULID_USER,
        ], $this->getInputContent('testFindUser'));

        $this->assertValidGraphQLResponse();
        $this->assertJsonResponseMatchesExpectations();
    }

    public function testNotFound(): void
    {
        $this->loginAsAdmin();

        $this->executeGraphQL([
            'uid' => '01H26252WYSK0MJN07YX2M9BWA',
        ], $this->getInputContent('testFindUser'));

        $this->assertGraphQLNotFoundResponse('User with UID "01H26252WYSK0MJN07YX2M9BWA" not found');
    }
}
