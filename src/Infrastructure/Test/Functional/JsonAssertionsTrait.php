<?php

declare(strict_types=1);

namespace App\Infrastructure\Test\Functional;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait JsonAssertionsTrait
{
    use ExpectationsTestTrait;

    protected static int $JSON_FLAGS = JSON_PRETTY_PRINT | JsonResponse::DEFAULT_ENCODING_OPTIONS;

    /**
     * To implement in consumer class
     */
    protected function getClientResponse(): Response
    {
        if (false !== get_parent_class($this) && method_exists(get_parent_class($this), 'getClientResponse')) {
            return parent::getClientResponse();
        }

        throw new \LogicException(sprintf(
            'You must implement the %s method or pass an explicit response object',
            __METHOD__,
        ));
    }

    protected function assertJsonResponseMatchesExpectations(string $filePath = null, Response $response = null): void
    {
        $response = $response ?? $this->getClientResponse();

        $filePath = $filePath ?? $this->getTestCaseExpectationsPath('json');
        $json = static::normalizeJson(json_decode((string) $response->getContent(), true));

        self::updateExpectations($filePath, $json);

        self::assertJsonStringMatchesJsonString(
            rtrim((string) file_get_contents($filePath)),
            rtrim($json),
            "Failed asserting that string matches format description in \"$filePath\"",
        );
    }

    protected function assertJsonResponseMatchesFormat(string $format, Response $response = null): void
    {
        $response = $response ?? $this->getClientResponse();

        $json = static::normalizeJson(json_decode((string) $response->getContent(), true));

        Assert::assertStringMatchesFormat(
            rtrim($format),
            rtrim($json),
            'Failed asserting that string matches format',
        );
    }

    protected static function assertJsonStringMatchesJsonString(
        string $expectedJson,
        string $actualJson,
        string $message = ''
    ): void {
        Assert::assertJson($expectedJson);
        Assert::assertJson($actualJson);

        Assert::assertStringMatchesFormat(
            // Workaround the fact format placeholders are always in a json string:
            strtr(static::normalizeJson(json_decode($expectedJson)), ['"%d"' => '%d', '"%a"' => '%a', '"%A"' => '%A']),
            static::normalizeJson(json_decode($actualJson, true)),
            $message,
        );
    }

    /**
     * Encodes to a normalized format for comparisons
     */
    protected static function normalizeJson(mixed $content): string
    {
        return (string) preg_replace('/^(  +?)\\1(?=[^ ])/m', '$1', json_encode($content, static::$JSON_FLAGS) . PHP_EOL);
    }

    /**
     * Overridden to decode as array instead of object (better diff).
     *
     * {@inheritdoc}
     */
    public static function assertJsonStringEqualsJsonString(string $expectedJson, string $actualJson, string $message = ''): void
    {
        Assert::assertJson($expectedJson, $message);
        Assert::assertJson($actualJson, $message);

        $expected = json_decode($expectedJson, true);
        $actual = json_decode($actualJson, true);

        Assert::assertEquals($expected, $actual, $message);
    }
}
