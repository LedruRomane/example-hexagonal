<?php

declare(strict_types=1);

namespace App\Tests\Functional\GraphQL\User;

use App\Infrastructure\Test\Functional\Controller\GraphQLTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ListUsersTest extends GraphQLTestCase
{
    use ResetDatabase;
    use Factories;

    public function testUnauthenticated(): void
    {
        $this->executeGraphQL([], $this->getInputContent('listUsers'))->getContent();

        $this->assertGraphQLAccessDenied();
    }

    public function testAuthenticatedAsUser(): void
    {
        $this->loginAsUser();

        $this->executeGraphQL([], $this->getInputContent('listUsers'));

        $this->assertGraphQLAccessDenied();
    }

    public function testAuthenticatedAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->executeGraphQL([], $this->getInputContent('listUsers'));

        $this->assertValidGraphQLResponse();
        $this->assertJsonResponseMatchesExpectations();
    }
}
