<?php

declare(strict_types=1);

use ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Shopsys\CodingStandards\Sniffs\ConstantVisibilityRequiredSniff;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

/**
 * @param Symplify\EasyCodingStandard\Config\ECSConfig $ecsConfig
 */
return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rule(PhpdocToPropertyTypeFixer::class);
    $ecsConfig->skip(
        [
            PhpdocToPropertyTypeFixer::class => [
                __DIR__ . '/packages/*',
                __DIR__ . '/project-base/src/*',
                __DIR__ . '/project-base/app/*',
                __DIR__ . '/project-base/tests/App/Acceptance/*',
                __DIR__ . '/utils/*',
            ],
            DeclareStrictTypesFixer::class => [
                __DIR__ . '/packages/*',
                __DIR__ . '/utils/*',
            ],
            ConstantVisibilityRequiredSniff::class => [
                __DIR__ . '/project-base/src/*',
                __DIR__ . '/project-base/tests/App/*',
            ],
            ForceLateStaticBindingForProtectedConstantsSniff::class => [
                __DIR__ . '/project-base/src/*',
                __DIR__ . '/project-base/tests/App/*',
            ],
            FunctionLengthSniff::class => [
                __DIR__ . '/utils/releaser/src/ReleaseWorker/Release/CreateAndPushGitTagsExceptProjectBaseReleaseWorker.php',
            ],
            CyclomaticComplexitySniff::class . '.MaxExceeded' => [
                __DIR__ . '/packages/framework/src/Model/Product/Search/ProductElasticsearchConverter.php',
            ],
        ]
    );

    $ecsConfig->import(__DIR__ . '/packages/*/ecs.php');
    $ecsConfig->import(__DIR__ . '/project-base/ecs.php');
};
