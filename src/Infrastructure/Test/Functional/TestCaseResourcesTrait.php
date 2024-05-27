<?php

declare(strict_types=1);

namespace App\Infrastructure\Test\Functional;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * Used to manipulate test cases resources paths (e.g: fixtures, expectations, inputs, ...)
 * This is useful for tests relying on resources based on such convention:
 *
 * ├── SearchTracksTest
 * │   ├── expected
 * │   │   ├── testSearch\ with\ data\ set\ "full\ args".json
 * │   │   ├── testSearch\ with\ data\ set\ "min\ args".json
 * │   │   └── testSearchInvalid.json
 * │   ├── fixtures
 * │   │   └── tracks.php
 * │   └── inputs
 * │       ├── testSearch\ with\ data\ set\ "full\ args".graphql
 * │       ├── testSearch\ with\ data\ set\ "min\ args".graphql
 * │       └── testSearchInvalid.graphql
 * └── SearchTracksTest.php
 */
trait TestCaseResourcesTrait
{
    /**
     * @var string
     *
     * @internal Setup by setUpTestCaseResourcePath with the current test file path
     *
     * @see getTestCaseResourcePath
     * @see setUpTestCaseResourcePath
     */
    private static $testCaseResourcePath;

    /**
     * @beforeClass
     *
     * @internal
     */
    public static function setUpTestCaseResourcePath(): void
    {
        self::$testCaseResourcePath = substr((string) (new \ReflectionClass(static::class))->getFileName(), 0, -4);
    }

    /** Override per class needs. E.g: php, yaml, json, graphql, ... */
    protected static function getDefaultInputFormat(): string
    {
        return 'json';
    }

    /** Override per class needs. E.g: php, yaml, ... */
    protected static function getDefaultExpectationsFormat(): string
    {
        return 'json';
    }

    /** Override per class needs. E.g: json, dump, ... */
    protected static function getDefaultFixturesFormat(): string
    {
        return 'php';
    }

    /**
     * @return string The test case file path, without extension
     */
    protected static function getTestCaseResourcePath(): string
    {
        return self::$testCaseResourcePath;
    }

    /**
     * Get the directory where to find fixtures for this test case
     */
    protected function getFixturesDir(): string
    {
        $testCasePath = self::getTestCaseResourcePath();

        return "$testCasePath/fixtures";
    }

    /**
     * Get the directory where to find fixtures for this test case
     */
    protected function getFixturesPath(string $name, string $ext = null): string
    {
        $testCasePath = self::getTestCaseResourcePath();

        return "$testCasePath/fixtures/$name." . ($ext ?? static::getDefaultFixturesFormat());
    }

    /**
     * Get by convention the fixtures file path for current test case
     */
    protected function getTestCaseFixturesPath(string $ext = null, bool $withDataSetName = true): string
    {
        $testCaseName = strtr($this->getName($withDataSetName), ['"' => '(']);

        return "{$this->getFixturesDir()}/{$testCaseName}." . ($ext ?? static::getDefaultFixturesFormat());
    }

    /**
     * Get the directory where to find expectations for this test case
     */
    protected function getExpectationsDir(): string
    {
        $testCasePath = self::getTestCaseResourcePath();

        return "$testCasePath/expected";
    }

    /**
     * Get the file path where to find expectations for this test case
     */
    protected function getExpectationsPath(string $name, string $ext = null): string
    {
        return "{$this->getExpectationsDir()}/$name." . ($ext ?? static::getDefaultExpectationsFormat());
    }

    /**
     * Get the directory where to find inputs for this test case
     */
    protected static function getInputsDir(): string
    {
        $testCasePath = self::getTestCaseResourcePath();

        return "$testCasePath/inputs";
    }

    /**
     * Get by convention the input file with given name and ext
     */
    protected function getInputPath(string $name, string $ext = null): string
    {
        $inputDir = static::getInputsDir();

        return "$inputDir/$name." . ($ext ?? static::getDefaultInputFormat());
    }

    /**
     * Get by convention the content from an input file with given name and ext
     */
    protected function getInputContent(string $name, string $ext = null): string
    {
        $content = file_get_contents($path = $this->getInputPath($name, $ext));

        if ($content === false) {
            throw new \LogicException("The file at path \"$path\" does not exists or cannot be read");
        }

        return $content;
    }

    /**
     * Get by convention the test case input file content
     *
     * @param bool $withDataSetName Includes the current data set name when using a data provider (the yielded key)
     */
    protected function getTestCaseInputContent(string $ext = null, bool $withDataSetName = true): string
    {
        $content = file_get_contents($path = $this->getTestCaseInputPath($ext ?? static::getDefaultInputFormat(), $withDataSetName));

        if ($content === false) {
            throw new \LogicException("The file at path \"$path\" does not exists or cannot be read");
        }

        return $content;
    }

    /**
     * Get by convention the input file path for current test case
     *
     * @param bool $withDataSetName Includes the current data set name when using a data provider (the yielded key)
     */
    protected function getTestCaseInputPath(string $ext = null, bool $withDataSetName = true): string
    {
        return $this->getInputPath($this->getName($withDataSetName), $ext ?? static::getDefaultInputFormat());
    }

    /**
     * Get by convention the input PHP file returned result with given name
     *
     * @return mixed The PHP file returned result
     */
    protected function getPhpInput(string $name)
    {
        return include $this->getInputPath($name, 'php');
    }

    /**
     * Get by convention the input CSV file returned result with given name
     *
     * @return string The CSV file returned result
     */
    protected function getCsvInput(string $name): string
    {
        return $this->getInputPath($name, 'csv');
    }

    /**
     * Copy a file from an input folder into a tmp directory (or provided target location).
     * In case an input file susceptible to me removed or altered by a process,
     * use this method in order to avoid altering a versioned file.
     *
     * @return string The new tmp file path
     */
    protected static function copyInputFile(string $filename, string $targetPath = null): string
    {
        $inputPath = Path::join(self::getInputsDir(), $filename);

        if (null === $targetPath) {
            copy($inputPath, $newPath = Path::join(sys_get_temp_dir(), $filename));

            return $newPath;
        }

        (new Filesystem())->copy($inputPath, $targetPath, overwriteNewerFiles: true);

        return $targetPath;
    }

    /**
     * Get by convention the expectations file path for current test case
     *
     * @param string|null $suffix Use a suffix appended (before the file extension) to the test case expectations
     *                            filename whenever you need to compare different things inside a same test case.
     *                            E.g: the JSON responded by your API + the HTTP call made to a third-party.
     */
    protected function getTestCaseExpectationsPath(
        string $ext = null,
        string $suffix = null,
        bool $withDataSetName = true
    ): string {
        $ext = $ext ?? static::getDefaultExpectationsFormat();
        $suffix = $suffix ?? '';
        $testCaseName = strtr($this->getName($withDataSetName), ['"' => '#']);

        return "{$this->getExpectationsDir()}/{$testCaseName}$suffix.$ext";
    }
}
