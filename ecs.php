<?php

declare(strict_types=1);

use ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Shopsys\CodingStandards\Sniffs\ConstantVisibilityRequiredSniff;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\UselessIfConditionWithReturnSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

/**
 * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $parameters = $containerConfigurator->parameters();

    $services->set(PhpdocToPropertyTypeFixer::class);

    $services->set(DocCommentSpacingSniff::class)
        ->property('linesCountBetweenDifferentAnnotationsTypes', 0);

    $services->set(UselessIfConditionWithReturnSniff::class);

    $parameters->set(
        Option::SKIP,
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

    $containerConfigurator->import(__DIR__ . '/packages/*/ecs.php');
    $containerConfigurator->import(__DIR__ . '/project-base/ecs.php');
};
