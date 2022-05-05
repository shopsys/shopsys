<?php

declare(strict_types=1);

use ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff;
use Shopsys\CodingStandards\CsFixer\ForbiddenPrivateVisibilityFixer;
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

    $containerConfigurator->import(SetList::PSR_12);

    $parameters->set(
        Option::SKIP,
        [
            __DIR__ . '/src/Resources/views/Migration/migration.php.twig',
            ObjectIsCreatedByFactorySniff::class => [
                __DIR__ . '/tests/*',
            ],
            FunctionLengthSniff::class => [
                __DIR__ . '/tests/Unit/Component/Doctrine/SchemaDiffFilterTest.php',
                __DIR__ . '/tests/Unit/Component/Doctrine/Migrations/MigrationsLockComparatorTest.php',
            ],
        ]
    );

    // this package is meant to be extensible using class inheritance, so we want to avoid private visibilities in these namespaces
    $services->set('forbidden_private_visibility_fixer.framework', ForbiddenPrivateVisibilityFixer::class)
        ->call('configure', [
            [
                'analyzed_namespaces' => [
                    'Shopsys\MigrationBundle\Command',
                    'Shopsys\MigrationBundle\Component',
                ],
            ],
        ]);

    $services->set(ForceLateStaticBindingForProtectedConstantsSniff::class);

    $containerConfigurator->import(__DIR__ . '/vendor/shopsys/coding-standards/ecs.php', null, true);
};
