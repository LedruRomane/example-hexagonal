<?php

declare(strict_types=1);

use App\Kernel;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

require \dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(\dirname(__DIR__) . '/config/bootstrap.php')) {
    require \dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(\dirname(__DIR__) . '/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Should update expectations (api outputs, dumps, ...) automatically or not.
// Don't blindly update, check the diff!
\define('UPDATE_EXPECTATIONS', filter_var(getenv('UPDATE_EXPECTATIONS') ?: getenv('UP'), FILTER_VALIDATE_BOOLEAN));

const TEST_DIR = __DIR__;

(new Filesystem())->remove([__DIR__ . '/../var/cache/test/']);

$kernel = new Kernel('test', false);
$kernel->boot();

$doctrine = $kernel->getContainer()->get('doctrine');

$connectionName = $doctrine->getDefaultConnectionName();
$managerName = $doctrine->getDefaultManagerName();
$manager = $doctrine->getManager($managerName);

$shouldTryToUpdateDatabase = false;

try {
    $metadata = $manager->getMetadataFactory()->getAllMetadata();
    $schemaTool = new SchemaTool($manager);
    $sqls = $schemaTool->getUpdateSchemaSql($metadata, true);
    $shouldTryToUpdateDatabase = 0 !== \count($sqls);
} catch (\Exception $ex) {
    $shouldTryToUpdateDatabase = true;
}

if ($shouldTryToUpdateDatabase) {
    $application = new Application($kernel);
    $output = new ConsoleOutput();

    // Drop old database if it exists:
    $input = new ArrayInput([
        'command' => 'doctrine:database:drop',
        '--connection' => $connectionName,
        '--force' => true,
        '--if-exists' => true,
    ]);
    $input->setInteractive(false);
    $application->doRun($input, $output);

    // Re-create the database from scratch:
    $input = new ArrayInput([
        'command' => 'doctrine:database:create',
        '--connection' => $connectionName,
    ]);
    $input->setInteractive(false);
    $application->doRun($input, $output);

    // Re-create the schema:
    $input = new ArrayInput([
        'command' => 'doctrine:schema:create',
        '--em' => $managerName,
    ]);
    $input->setInteractive(false);
    $application->doRun($input, $output);
}

$kernel->shutdown();

unset($loader, $kernel, $application, $metadata, $schemaTool, $sqls, $manager);
