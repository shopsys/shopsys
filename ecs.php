<?php

declare(strict_types=1);

use ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Classes\ValidClassNameSniff;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Shopsys\CodingStandards\Sniffs\ConstantVisibilityRequiredSniff;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use SlevomatCodingStandard\Sniffs\Classes\ParentCallSpacingSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DeprecatedAnnotationDeclarationSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\EarlyExitSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\UselessIfConditionWithReturnSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
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
            SetList::PHP_70,
            SetList::PHP_71,
            SetList::PSR_12,
            SetList::DEAD_CODE,
            SetList::CLEAN_CODE,
            SetList::ARRAY,
            SetList::COMMENTS,
            SetList::CONTROL_STRUCTURES,
            SetList::DOCBLOCK,
            SetList::NAMESPACES,
            SetList::STRICT,
        ]
    );

    $parameters->set(
        Option::SKIP,
        [
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

    $services->set(ValidClassNameSniff::class);

    $services->set(NoUselessElseFixer::class);

    $services->set(AssignmentInConditionSniff::class);

    $services->set(DisallowEmptySniff::class);

    $services->set(EarlyExitSniff::class)
        ->property('ignoreStandaloneIfInScope', true)
        ->property('ignoreOneLineTrailingIf', true)
        ->property('ignoreTrailingIfWithOneInstruction', true);

    $services->set(ParentCallSpacingSniff::class)
        ->property('linesCountBeforeParentCall', 1)
        ->property('linesCountAfterParentCall', 1);

    $services->set(ReferenceUsedNamesOnlySniff::class)
        ->property('allowPartialUses', true);

    $services->set(DeprecatedAnnotationDeclarationSniff::class);

    $services->set(DocCommentSpacingSniff::class)
        ->property('linesCountBetweenDifferentAnnotationsTypes', 0);

    $services->set(UselessIfConditionWithReturnSniff::class);

    $containerConfigurator->import(__DIR__ . '/packages/*/ecs.php');
    $containerConfigurator->import(__DIR__ . '/project-base/ecs.php');
};
