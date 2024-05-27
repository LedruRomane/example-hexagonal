<?php

declare(strict_types=1);

namespace App\Tests\Functional\GraphQL\User;

use App\Infrastructure\Test\Functional\Controller\GraphQLTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ForgotPasswordTest extends GraphQLTestCase
{
    use ResetDatabase;
    use Factories;
    use MailerAssertionsTrait;

    public function testSuccessfulForgotPassword(): void
    {
        $this->executeGraphQL([
            'email' => 'admin@example.com',
        ], $this->getInputContent('testForgotPassword'));

        $this->assertValidGraphQLResponse();
        $this->assertJsonResponseMatchesExpectations();

        self::assertQueuedEmailCount(1, message: 'An email should have been sent to the user');

        $email = self::getMailerMessage();
        self::assertEmailAddressContains($email, 'To', 'admin@example.com');
        self::assertEmailAddressContains($email, 'From', 'no-reply@gmail.com');
        self::assertEmailHtmlBodyContains($email, 'http://test.front.example.com/reset-password/');
    }

    public function testForgotPasswordWithNonExistingEmail(): void
    {
        $this->executeGraphQL([
            'email' => 'non-existing@example.com',
        ], $this->getInputContent('testForgotPassword'));

        $this->assertValidGraphQLResponse();
        $this->assertJsonResponseMatchesExpectations();

        self::assertQueuedEmailCount(0, message: 'No email should have been sent');
    }
}
