<?php

declare(strict_types=1);

use ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\NamingConventions\ValidVariableNameSniff;
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
    $services->set('forbidden_private_visibility_fixer.product_feed_heureka', ForbiddenPrivateVisibilityFixer::class)
        ->call('configure', [
            [
                'analyzed_namespaces' => [
                    'Shopsys\ProductFeed\HeurekaBundle\Model',
                ],
            ],
        ]);

    $ecsConfig->skip([
        FunctionLengthSniff::class => [
            __DIR__ . '/src/DataFixtures/HeurekaProductDataFixture.php',
            __DIR__ . '/tests/Unit/HeurekaFeedTest.php',
        ],
        ValidVariableNameSniff::class . '.MemberNotCamelCaps' => [
            __DIR__ . '/src/Model/HeurekaCategory/HeurekaCategoryDownloader.php',
        ],
        ValidVariableNameSniff::class . '.NotCamelCaps' => [
            __DIR__ . '/src/Model/HeurekaCategory/HeurekaCategoryDownloader.php',
        ],
        ObjectIsCreatedByFactorySniff::class => [
            __DIR__ . '/tests/*',
        ],
    ]);

    $ecsConfig->import(__DIR__ . '/vendor/shopsys/coding-standards/ecs.php', null, true);
};
