<?php

declare(strict_types=1);

use ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

/**
 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        Option::SETS,
        [
            SetList::PSR_12,
        ]
    );

    $parameters->set(
        Option::EXCLUDE_PATHS,
        [
            __DIR__ . '/src/Resources/views/Migration/migration.php.twig',
        ]
    );

    $parameters->set(
        Option::SKIP,
        [
            ObjectIsCreatedByFactorySniff::class => [
                __DIR__ . '/tests/*',
            ],
            FunctionLengthSniff::class => [
                __DIR__ . '/tests/Unit/Component/Doctrine/SchemaDiffFilterTest.php',
                __DIR__ . '/tests/Unit/Component/Doctrine/Migrations/MigrationsLockComparatorTest.php',
            ],
        ]
    );

    $services->set(ForceLateStaticBindingForProtectedConstantsSniff::class);

    $containerConfigurator->import(__DIR__ . '/vendor/shopsys/coding-standards/ecs.php', null, true);
};
