<?php

declare(strict_types=1);

namespace App\Infrastructure\Test\Listener;

use PHPUnit\Framework\RiskyTestError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Warns every time a test expectation file was changed in order to prevent the developer to blindly commit changes.
 *
 * **Requires**
 * - providing the UPDATE_EXPECTATIONS global constant from your tests bootstraping file (usually populated from an env var).
 */
class UpdateExpectationsGuardListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * @internal
     *
     * @see \App\Infrastructure\Test\Functional\ExpectationsTestTrait::updateExpectations
     */
    public static bool $hasUpdatedAnExpectation = false;

    public static bool $hasAlreadyShownWarning = false;

    public function endTest(Test $test, float $time): void
    {
        if (
            self::enabled() &&
            $test instanceof TestCase &&
            property_exists($test, 'updateExpectationsWarning') &&
            null !== $test::$updateExpectationsWarning
        ) {
            $warning = $test::$updateExpectationsWarning;
            $formatter = new OutputFormatterStyle('yellow');
            $warning = $formatter->apply($warning);
            $result = $test->getTestResultObject();
            \assert($result !== null);
            $result->addFailure($test, new RiskyTestError($warning), $time);
            $test::$updateExpectationsWarning = null;
        }
    }

    public function endTestSuite(TestSuite $suite): void
    {
        if (!self::$hasAlreadyShownWarning && self::$hasUpdatedAnExpectation) {
            self::$hasAlreadyShownWarning = true;
            register_shutdown_function(static function (): void {
                $formatter = new OutputFormatter(true, [
                    'success' => new OutputFormatterStyle('green'),
                    'warning' => new OutputFormatterStyle('yellow'),
                ]);

                (new ConsoleOutput())->writeln((string) $formatter->format(<<<TXT

                <success>✔   One or more expectations files were updated since you used UPDATE_EXPECTATIONS=1 (or UP=1)</success>
                <warning>🙏  Please double-check the diff (if any) before commit!</warning>


                TXT));

                self::$hasUpdatedAnExpectation = false;
            });
        }
    }

    public static function enabled(): bool
    {
        return true === \defined('UPDATE_EXPECTATIONS') && true === \constant('UPDATE_EXPECTATIONS');
    }
}
