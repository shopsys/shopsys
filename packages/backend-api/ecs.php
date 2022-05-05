<?php

declare(strict_types=1);

use ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff;
use Shopsys\CodingStandards\CsFixer\ForbiddenPrivateVisibilityFixer;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

/**
 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $parameters = $containerConfigurator->parameters();

    $services->set(ForceLateStaticBindingForProtectedConstantsSniff::class);

    // this package is meant to be extensible using class inheritance, so we want to avoid private visibilities in the model namespace
    $services->set('forbidden_private_visibility_fixer.backend_api', ForbiddenPrivateVisibilityFixer::class)
        ->call('configure', [['analyzed_namespaces' => ['Shopsys\BackendApiBundle']]]);

    $parameters->set(
        Option::SKIP,
        [
            ObjectIsCreatedByFactorySniff::class => [
                __DIR__ . '/tests/*',
            ],
            FunctionLengthSniff::class => [
                __DIR__ . '/src/Controller/**/*Validator.php',
                __DIR__ . '/install/tests/App/Smoke/BackendApiCreateProductTest.php',
            ],
        ]
    );

    $containerConfigurator->import(__DIR__ . '/vendor/shopsys/coding-standards/ecs.php', null, true);
};
