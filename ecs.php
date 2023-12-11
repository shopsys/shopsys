<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Shopsys\CodingStandards\Helper\CyclomaticComplexitySniffSetting;
use Shopsys\CodingStandards\Sniffs\ConstantVisibilityRequiredSniff;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

/**
 * @param Symplify\EasyCodingStandard\Config\ECSConfig $ecsConfig
 */
return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->rule(PhpdocToPropertyTypeFixer::class);
    $ecsConfig->skip(
        [
            PhpdocToPropertyTypeFixer::class => [
                __DIR__ . '/project-base/app/src/*',
                __DIR__ . '/project-base/app/app/*',
                __DIR__ . '/project-base/app/tests/App/Acceptance/*',
                __DIR__ . '/utils/*',
            ],
            DeclareStrictTypesFixer::class => [
                __DIR__ . '/utils/*',
            ],
            ConstantVisibilityRequiredSniff::class => [
                __DIR__ . '/project-base/app/src/*',
                __DIR__ . '/project-base/app/tests/App/*',
            ],
            ForceLateStaticBindingForProtectedConstantsSniff::class => [
                __DIR__ . '/project-base/app/src/*',
                __DIR__ . '/project-base/app/tests/App/*',
            ],
            FunctionLengthSniff::class => [
                __DIR__ . '/utils/releaser/src/ReleaseWorker/Release/CreateAndPushGitTagsExceptProjectBaseReleaseWorker.php',
            ],
        ]
    );

    $ecsConfig->import(__DIR__ . '/packages/*/ecs.php');
    $ecsConfig->import(__DIR__ . '/project-base/app/ecs.php');

    $ecsConfig->ruleWithConfiguration(CyclomaticComplexitySniff::class, [
        'absoluteComplexity' => CyclomaticComplexitySniffSetting::DEFAULT_ABSOLUTE_COMPLEXITY,
    ]);
};
