<?php

declare(strict_types=1);

use App\Domain\User\Security\PasswordHasherInterface;
use App\Infrastructure\Bridge\GraphQL\ExpressionLanguage\TranslateExpressionFunction;
use App\Infrastructure\Common\Serializer\SkippingInvalidUidNormalizer;
use App\Infrastructure\Security\User\Identity;
use App\Infrastructure\Test\Fixtures\Story\GlobalTestsStory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface as SymfonyPasswordHasherInterface;

// This file is the entry point to configure your own services.
// Files in the packages/ subdirectory configure your dependencies.

return static function (ContainerConfigurator $sc) {
    // Put parameters here that don't need to change on each machine where the app is deployed
    // https://symfony.com/doc/current/best_practices.html#use-parameters-for-application-configuration
    $sc->parameters()
        ->set('default_locale', 'fr')
        ->set('app_front_host', env('APP_FRONT_HOST'))
        ->set('app_front_scheme', env('APP_FRONT_SCHEME'))
    ;

    // Put parameters here that don't need to change on each machine where the app is deployed
    // https://symfony.com/doc/current/best_practices.html#use-parameters-for-application-configuration
    // default configuration for services in *this* file
    $services = $sc->services()
        ->defaults()
            ->autowire() // Automatically injects dependencies in your services.
            ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
        // ->bind('string $projectDir', '%kernel.project_dir%')
    ;

    // makes classes in src/ available to be used as services
    // this creates a service per class whose id is the fully-qualified class name
    $services->load('App\\', '%kernel.project_dir%/src/')
        ->exclude([
            '%kernel.project_dir%/src/Kernel.php',
            '%kernel.project_dir%/src/Infrastructure/Test/**/*.php',
        ])

        ->set(SkippingInvalidUidNormalizer::class)
        ->tag('serializer.normalizer', ['priority' => -880])

        ->set(TranslateExpressionFunction::class)
        ->tag('overblog_graphql.expression_function')
    ;

    // Security
    $services
        /*
         * Password hasher for our Identity class to hash password of our users without an instance,
         * just the plain text password.
         */
        ->set('identity_password_hasher', SymfonyPasswordHasherInterface::class)
        ->factory([service(PasswordHasherFactoryInterface::class), 'getPasswordHasher'])
        ->args([Identity::class])
        /*
         * Create our own PasswordHasherInterface adapter from Symfony's PasswordHasherInterface.
         * @see https://symfony.com/blog/new-in-symfony-6-3-dependency-injection-improvements#generating-adapters-for-functional-interfaces
         */
        ->set(PasswordHasherInterface::class, PasswordHasherInterface::class)
        ->fromCallable([service('identity_password_hasher'), 'hash'])
    ;

    // Command buses:
    $services
        // query handlers:
        ->load('App\\Application\\', '%kernel.project_dir%/src/Application/**/Handler/**/*QueryHandler.php')
            ->tag('messenger.message_handler', ['bus' => 'messenger.bus.queries'])

        // command handlers:
        ->load('App\\Application\\', '%kernel.project_dir%/src/Application/**/Handler/**/*CommandHandler.php')
            ->tag('messenger.message_handler', ['bus' => 'messenger.bus.commands'])

        // event handlers:
        ->load('App\\Application\\', '%kernel.project_dir%/src/Application/**/Handler/**/*EventHandler.php')
            ->tag('messenger.message_handler', ['bus' => 'messenger.bus.commands'])
    ;

    // Test specific services:
    if ($sc->env() === 'test') {
        $services->set(GlobalTestsStory::class);
    }
};
