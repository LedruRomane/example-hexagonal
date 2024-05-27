<?php

declare(strict_types=1);

namespace App\Infrastructure\Test\Functional\Controller;

use App\Infrastructure\Test\Functional\DoctrineTrait;
use App\Infrastructure\Test\Functional\GraphQLTestTrait;
use App\Infrastructure\Test\Functional\JwtLoginTestTrait;
use Symfony\Component\HttpFoundation\Response;

abstract class GraphQLTestCase extends ControllerTestCase
{
    use GraphQLTestTrait;
    use JwtLoginTestTrait;
    use DoctrineTrait;

    protected static int $JSON_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;

    protected function assertGraphQLResponseMatchesExpectations(Response $response = null, string $filePath = null): void
    {
        $this->assertJsonResponseMatchesExpectations($filePath, $response);
    }

    protected static function normalizeJson(mixed $content): string
    {
        // Removes GraphQL debug mode trace from response content if it exists:
        if (\is_array($content) && isset($content['errors'])) {
            array_walk($content['errors'], static function (&$error) {
                unset($error['trace']);
            });
        }

        return parent::normalizeJson($content);
    }
}
