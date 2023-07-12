<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Shopsys\CodingStandards\CsFixer\ForbiddenPrivateVisibilityFixer;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff;
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
    $services->set('forbidden_private_visibility_fixer.product_feed_zbozi', ForbiddenPrivateVisibilityFixer::class)
        ->call('configure', [
            [
                'analyzed_namespaces' => [
                    'Shopsys\ProductFeed\ZboziBundle\Model',
                ],
            ],
        ]);

    $ecsConfig->skip([
        FunctionLengthSniff::class => [
            __DIR__ . '/src/DataFixtures/ZboziPluginDataFixture.php',
            __DIR__ . '/tests/Unit/ZboziFeedTest.php',
        ],
        ObjectIsCreatedByFactorySniff::class => [
            __DIR__ . '/tests/*',
        ],
        PhpdocToPropertyTypeFixer::class => [
            __DIR__ . '/src/*',
        ],
        DeclareStrictTypesFixer::class => [
            __DIR__ . '/src/*',
        ],
    ]);

    $ecsConfig->import(__DIR__ . '/vendor/shopsys/coding-standards/ecs.php', null, true);
};
