<?php

declare(strict_types=1);

namespace App\Infrastructure\Test\Functional;

use App\Infrastructure\Bridge\GraphQL\Error\AccessDeniedError;
use App\Infrastructure\Bridge\GraphQL\Error\CustomUserError;
use App\Infrastructure\Bridge\GraphQL\Error\ForbiddenError;
use App\Infrastructure\Bridge\GraphQL\Error\InvalidPayloadError;
use App\Infrastructure\Bridge\GraphQL\Error\NotFoundError;
use Overblog\GraphQLBundle\Request\ParserInterface;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

trait GraphQLTestTrait
{
    use TestCaseResourcesTrait;

    /** Change according to your app */
    protected static string $graphqlEndpoint = '/graphql/';

    private static OutputFormatterInterface $formatter;

    /**
     * @beforeClass
     *
     * @internal
     */
    public static function setUpGraphQLTestTrait(): void
    {
        self::$formatter = new OutputFormatter(true, [
            'path' => new OutputFormatterStyle('yellow', null, ['underscore']),
            'error' => new OutputFormatterStyle('red'),
            'error_path' => new OutputFormatterStyle('red', null, ['underscore']),
            'hint' => new OutputFormatterStyle('black', 'white'),
        ]);
    }

    protected static function getDefaultInputFormat(): string
    {
        return 'graphql';
    }

    protected function getTestCaseGraphqlInput(bool $withDataSetName = true): string
    {
        return $this->getTestCaseInputContent('graphql', $withDataSetName);
    }

    abstract protected static function getKernelBrowser(): KernelBrowser;

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

    /**
     * @param array<string, mixed>  $variables
     * @param array<string, string> $files
     */
    protected function executeGraphQL(array $variables = [], string $query = null, array $files = []): Response
    {
        if ($query === null && file_exists($this->getTestCaseInputPath('graphql'))) {
            $query = $this->getTestCaseInputContent('graphql');
        }

        if ($query === null) {
            throw new \LogicException(
                'GraphQL Query cannot be loaded. ' .
                "Perhaps a \"{$this->getTestCaseInputPath('graphql')}\" file is missing?"
            );
        }

        $payload = [
            'query' => $query,
            'variables' => $variables,
        ];

        // Without file upload:
        if (\count($files) === 0) {
            ($kernelBrowser = static::getKernelBrowser())->request(
                Request::METHOD_POST,
                static::$graphqlEndpoint,
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                (string) json_encode($payload)
            );

            return $kernelBrowser->getResponse();
        }

        // With file upload:
        $mapContents = [];
        $uploadFiles = [];

        foreach ($files as $name => $path) {
            $uploadFiles[] = new UploadedFile($path, basename($path), (string) mime_content_type($path));
            $mapContents[] = ["variables.$name"];
        }

        $payload = [
            'query' => $query,
            'operations' => json_encode($payload),
            'map' => $mapContents,
        ];

        ($kernelBrowser = static::getKernelBrowser())->request(
            Request::METHOD_POST,
            static::$graphqlEndpoint,
            $payload,
            $uploadFiles,
            ['CONTENT_TYPE' => ParserInterface::CONTENT_TYPE_FORM_DATA],
        );

        return $kernelBrowser->getResponse();
    }

    /**
     * @param bool $ignoreAppErrors By default, it fails on any error.
     *                              If true, it'll consider responses with app errors (like violations) as a valid response,
     *                              allowing to perform assertions on the expected violations after.
     *
     * @return array decoded response content
     */
    public function assertValidGraphQLResponse(Response $response = null, bool $ignoreAppErrors = false): array
    {
        $response = $response ?? $this->getClientResponse();

        static $formatErrors;

        if (!$formatErrors) {
            $formatErrors = static function (array $errors): string {
                return implode(PHP_EOL, array_map(static function ($error): string {
                    $trace = isset($error['trace'])
                        ? PHP_EOL . self::format($error['trace'], '🐛 Trace')
                        : self::$formatter->format(' <hint>(use APP_DEBUG=1 for trace & debug message)</hint>')
                    ;
                    $path = isset($error['path']) ? self::$formatter->format(
                        '<error_path>' . implode('.', $error['path']) . '</error_path>: '
                    ) : null;

                    $details = null;
                    if (InvalidPayloadError::ERROR_CODE === ($error['code'] ?? null)) {
                        $details = PHP_EOL . PHP_EOL . self::indent(self::formatApiProblemDetail($error['api_problem']['detail'])) . PHP_EOL;
                        $details .= self::format($error['api_problem']['violations'], 'ℹ️  Details');
                    }

                    $message = $error['debugMessage'] ?? $error['message'];
                    $message = self::$formatter->format("<error>$message</error>");

                    return '  - ' . $path . $message . $details . $trace;
                }, $errors));
            };
        }

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        /** @var array $content */
        $content = json_decode((string) $response->getContent(), true);

        if (\array_key_exists('errors', $content)) {
            if (!$ignoreAppErrors) {
                Assert::fail('Failed asserting that the graphql response does not have any errors: ' . PHP_EOL . $formatErrors($content['errors']));
            }
        }

        return $content;
    }

    public function assertGraphQLInvalidPayloadResponse(Response $response = null): void
    {
        $content = $this->assertValidGraphQLResponse($response, true);

        Assert::assertSame(InvalidPayloadError::ERROR_CODE, $content['errors'][0]['code'] ?? null, 'expected an invalid payload error code');
    }

    public function assertGraphQLNotFoundResponse(string $expectedMsgFormat = null, Response $response = null): void
    {
        $content = $this->assertValidGraphQLResponse($response, true);

        Assert::assertSame(NotFoundError::ERROR_CODE, $content['errors'][0]['code'] ?? null, 'expected a not found error code');

        if (\is_string($expectedMsgFormat)) {
            Assert::assertStringMatchesFormat($expectedMsgFormat, $content['errors'][0]['message'] ?? null);
        }
    }

    public function assertGraphQLAccessDenied(Response $response = null): void
    {
        $content = $this->assertValidGraphQLResponse($response, true);

        Assert::assertSame(AccessDeniedError::ERROR_CODE, $content['errors'][0]['code'] ?? null, 'expected an access denied error code');
    }

    public function assertGraphQLForbiddenResponse(string $expectedMsgFormat = null, Response $response = null): void
    {
        $content = $this->assertValidGraphQLResponse($response, true);

        Assert::assertSame(ForbiddenError::ERROR_CODE, $content['errors'][0]['code'] ?? null, 'expected a forbidden error code');

        if (\is_string($expectedMsgFormat)) {
            Assert::assertStringMatchesFormat($expectedMsgFormat, $content['errors'][0]['message'] ?? null);
        }
    }

    public function assertGraphQLCustomUserErrorResponse(string $expectedMsgFormat = null, Response $response = null): void
    {
        $content = $this->assertValidGraphQLResponse($response, true);

        Assert::assertSame(CustomUserError::ERROR_CODE, $content['errors'][0]['code'] ?? null, 'expected a custom user error code');

        if (\is_string($expectedMsgFormat)) {
            Assert::assertStringMatchesFormat($expectedMsgFormat, $content['errors'][0]['message'] ?? null);
        }
    }

    public function assertErrorGraphQLResponse(string $expectedErrorCode, Response $response = null): void
    {
        $response = $response ?? $this->getClientResponse();

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        /** @var array $content */
        $content = json_decode((string) $response->getContent(), true);

        if (\array_key_exists('errors', $content)) {
            $errorCodes = array_map(static function ($element) {
                return $element['code'] ?? null;
            }, $content['errors']);

            Assert::assertContains(
                $expectedErrorCode,
                $errorCodes,
                'The graphql response does have errors but an expected one is not present: ' . $expectedErrorCode
            );
        } else {
            Assert::fail('Failed asserting that the graphql response does have any errors. Expected: ' . $expectedErrorCode);
        }
    }

    private static function format(array $trace, string $prefix = null, int $indentLevel = 4, int $limit = 5): string
    {
        $cloner = new VarCloner();
        $dumper = new CliDumper();
        $dumper->setColors(true);

        return PHP_EOL . self::indent("$prefix: ", $indentLevel) . PHP_EOL . self::indent(
            $dumper->dump($cloner->cloneVar(array_splice($trace, 0, $limit)), true) ?? '',
            $indentLevel
        );
    }

    private static function formatApiProblemDetail(string $detail): string
    {
        $lines = [];
        foreach (explode("\n", $detail) as $line) {
            [$path, $message] = explode(':', $line, 2);
            $lines[] = self::$formatter->format("<path>$path</path>: $message");
        }

        return implode(PHP_EOL, $lines);
    }

    private static function indent(string $string, int $level = 4): string
    {
        $indent = str_repeat(' ', $level);

        return $indent . implode(PHP_EOL . $indent, explode(PHP_EOL, $string));
    }
}
