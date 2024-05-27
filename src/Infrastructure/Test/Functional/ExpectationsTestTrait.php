<?php

declare(strict_types=1);

namespace App\Infrastructure\Test\Functional;

use App\Infrastructure\Test\Listener\UpdateExpectationsGuardListener;
use PHPUnit\Framework\Assert;
use Symfony\Component\Filesystem\Filesystem;

trait ExpectationsTestTrait
{
    use TestCaseResourcesTrait;

    /**
     * @internal
     *
     * @used-by UpdateExpectationsGuardListener
     */
    public static ?string $updateExpectationsWarning = null;

    /**
     * Updates given file with content if UPDATE_EXPECTATIONS is true.
     */
    protected static function updateExpectations(string $expectationsFilePath, string $actual): void
    {
        if (UpdateExpectationsGuardListener::enabled()) {
            $fs = new Filesystem();
            if (
                file_exists($expectationsFilePath) &&
                1 === preg_match('/\%e|\%s|\%S|\%a|\%A|\%w|\%i|\%d|\%x|\%f|\%c/', (string) file_get_contents($expectationsFilePath))
            ) {
                $relativePath = rtrim($fs->makePathRelative($expectationsFilePath, \dirname(self::getTestDir())), '/');
                self::$updateExpectationsWarning = <<<TXT

⚠️   Updating expectations in file "$relativePath" containing one or more format strings.
🙏  Please double-check the diff before commit!
TXT;
            }

            // Create the directory if not exists:
            $fs->mkdir(\dirname($expectationsFilePath));

            file_put_contents($expectationsFilePath, rtrim($actual) . "\n");
            UpdateExpectationsGuardListener::$hasUpdatedAnExpectation = true;
        }
    }

    public function assertFileMatchesExpectationsFile(
        string $filePath,
        ?string $expectationsFilePath,
        string $message = null
    ): void {
        $this->assertStringMatchesExpectationsFile(
            (string) file_get_contents($filePath),
            $expectationsFilePath,
            $message ?? "Failed asserting that file \"$filePath\" matches expectations in \"$expectationsFilePath\""
        );
    }

    private static function getTestDir(): string
    {
        if (!\defined('TEST_DIR')) {
            throw new \LogicException('You must set the TEST_DIR in your PHPUnit bootstrap file.');
        }

        return TEST_DIR;
    }

    public function assertStringMatchesExpectationsFile(
        string $actual,
        string $expectationsFilePath = null,
        string $message = null
    ): void {
        $expectationsFilePath = $expectationsFilePath ?? $this->getTestCaseExpectationsPath();

        static::updateExpectations($expectationsFilePath, $actual);

        Assert::assertStringMatchesFormat(
            rtrim((string) file_get_contents($expectationsFilePath)),
            rtrim($actual),
            $message ?? "Failed asserting that string matches expectations in \"$expectationsFilePath\"",
        );
    }
}
