<?php

declare(strict_types=1);

use Shopsys\CodingStandards\CsFixer\ForbiddenPrivateVisibilityFixer;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

/**
 * @param Symplify\EasyCodingStandard\Config\ECSConfig $ecsConfig
 */
return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rule(ForceLateStaticBindingForProtectedConstantsSniff::class);

    /*
     * this package is meant to be extensible using class inheritance,
     * so we want to avoid private visibilities in the model namespace
     */
    $services = $ecsConfig->services();
    $services->set('forbidden_private_visibility_fixer.read_model', ForbiddenPrivateVisibilityFixer::class)
        ->call('configure', [
            [
                'analyzed_namespaces' => [
                    'Shopsys\ReadModelBundle',
                ],
            ],
        ]);

    $ecsConfig->skip([
        ObjectIsCreatedByFactorySniff::class =>
            [
                __DIR__ . '/src/Product/Detail/ProductDetailViewElasticsearchFactory.php',
                __DIR__ . '/tests/*',
            ],
    ]);

    $ecsConfig->import(__DIR__ . '/vendor/shopsys/coding-standards/ecs.php', null, true);
};
