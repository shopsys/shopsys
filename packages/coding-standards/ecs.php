<?php

declare(strict_types=1);

use ObjectCalisthenics\Sniffs\Files\ClassTraitAndInterfaceLengthSniff;
use ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff;
use ObjectCalisthenics\Sniffs\Metrics\PropertyPerClassLimitSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\EmptyStatementSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\ForLoopShouldBeWhileLoopSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\ForLoopWithTestFunctionCallSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\JumbledIncrementerSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\UnconditionalIfStatementSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Commenting\FixmeSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Commenting\TodoSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Formatting\NoSpaceAfterCastSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Functions\CallTimePassByReferenceSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\ConstructorNameSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\DiscourageGotoSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\NoSilencedErrorsSniff;
use PHP_CodeSniffer\Standards\MySource\Sniffs\PHP\GetRequestDataSniff;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\InlineCommentSniff;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\NamingConventions\ValidClassNameSniff;
use PHP_CodeSniffer\Standards\PSR1\Sniffs\Methods\CamelCapsMethodNameSniff;
use PHP_CodeSniffer\Standards\PSR2\Sniffs\Methods\FunctionCallSignatureSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\NamingConventions\ValidVariableNameSniff as PhpCsValidVariableNameSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP\DisallowMultipleAssignmentsSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP\DisallowSizeFunctionsInLoopsSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP\EvalSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP\GlobalKeywordSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP\InnerFunctionsSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP\LowercasePHPFunctionsSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP\NonExecutableCodeSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Scope\StaticThisUsageSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Strings\DoubleQuoteUsageSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\CastSpacingSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\ControlStructureSpacingSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\LanguageConstructSpacingSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\LogicalOperatorSpacingSniff;
use PhpCsFixer\Fixer\Alias\EregToPregFixer;
use PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer;
use PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer;
use PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoTrailingCommaInSinglelineArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrailingCommaInMultilineArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\Basic\NonPrintableCharacterFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\NoPhp4ConstructorFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;
use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoTrailingCommaInListCallFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\FunctionTypehintSpaceFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoAccessFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoAliasTagFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoPackageFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\ReturnNotation\NoUselessReturnFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Semicolon\NoMultilineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Semicolon\SpaceAfterSemicolonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraConsecutiveBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use Shopsys\CodingStandards\CsFixer\ForbiddenDumpFixer;
use Shopsys\CodingStandards\CsFixer\MissingButtonTypeFixer;
use Shopsys\CodingStandards\CsFixer\OrmJoinColumnRequireNullableFixer;
use Shopsys\CodingStandards\CsFixer\Phpdoc\MissingParamAnnotationsFixer;
use Shopsys\CodingStandards\CsFixer\Phpdoc\MissingReturnAnnotationFixer;
use Shopsys\CodingStandards\CsFixer\Phpdoc\OrderedParamAnnotationsFixer;
use Shopsys\CodingStandards\CsFixer\RedundantMarkDownTrailingSpacesFixer;
use Shopsys\CodingStandards\Finder\FileFinder;
use Shopsys\CodingStandards\Helper\FqnNameResolver;
use Shopsys\CodingStandards\Helper\PhpToDocTypeTransformer;
use Shopsys\CodingStandards\Sniffs\ForbiddenDoctrineDefaultValueSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenDoctrineInheritanceSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenDumpSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenExitSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenSuperGlobalSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Shopsys\CodingStandards\Sniffs\ValidVariableNameSniff;
use SlevomatCodingStandard\Sniffs\Classes\UnusedPrivateElementsSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DisallowCommentAfterCodeSniff;
use SlevomatCodingStandard\Sniffs\Commenting\InlineDocCommentDeclarationSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff;
use SlevomatCodingStandard\Sniffs\Namespaces\FullyQualifiedClassNameInAnnotationSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\NullableTypeForNullDefaultValueSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSpacingSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\IndentDetector;
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

    $parameters->set(Option::LINE_ENDING, '\n');

    $parameters->set(Option::EXCLUDE_PATHS,
        [
            __DIR__ . '/tests/Unit/**/wrong/*',
            __DIR__ . '/tests/Unit/**/Wrong/*',
            __DIR__ . '/tests/Unit/**/correct/*',
            __DIR__ . '/tests/Unit/**/Correct/*',
            __DIR__ . '/tests/Unit/**/fixed/*',
        ]
    );

    $parameters->set(Option::SKIP,
        [
            UnusedPrivateElementsSniff::class => [
                '*/tests/Unit/CsFixer/OrmJoinColumnRequireNullableFixer/*',
            ],
            // private properties should not start with "_"
            PhpCsValidVariableNameSniff::class . '.PrivateNoUnderscore' => null,
            // it is not necessary to have blank line after control structure
            ControlStructureSpacingSniff::class . '.NoLineAfterClose' => null,
            // allow empty "catch (Exception $exception) { }"
            EmptyStatementSniff::class . '.DetectedCatch' => null,
            FunctionCallSignatureSniff::class . '.Indent' => null,
            DisallowEmptySniff::class => [
                '*/src/Yaml/CheckerServiceParametersShifter.php',
            ],
            ArrayOpenerAndCloserNewlineFixer::class => null,
            ArrayListItemNewlineFixer::class => null,
            DisallowCommentAfterCodeSniff::class . '.DisallowedCommentAfterCode' => null,
        ]
    );

    $services->defaults()->autowire();

    // helper services
    $services->set(FunctionsAnalyzer::class);
    $services->set(PhpToDocTypeTransformer::class);
    $services->set(FqnNameResolver::class);
    $services->set(NamespaceUsesAnalyzer::class);
    $services->set(IndentDetector::class);
    $services->set(FileFinder::class);

    // Slevomat Checkers
    $services->set(UnusedPrivateElementsSniff::class);
    $services->set(InlineDocCommentDeclarationSniff::class);
    $services->set(NullableTypeForNullDefaultValueSniff::class);
    $services->set(ReturnTypeHintSpacingSniff::class);

    // Shopsys Checkers
    $services->set(ForbiddenDumpFixer::class);
    $services->set(MissingButtonTypeFixer::class);
    $services->set(OrmJoinColumnRequireNullableFixer::class);
    $services->set(RedundantMarkDownTrailingSpacesFixer::class);
    $services->set(ObjectIsCreatedByFactorySniff::class);
    $services->set(ForbiddenDumpSniff::class);
    $services->set(ForbiddenExitSniff::class);
    $services->set(ForbiddenSuperGlobalSniff::class);
    $services->set(ForbiddenDoctrineInheritanceSniff::class);
    $services->set(ForbiddenDoctrineDefaultValueSniff::class);
    // method arguments and variables should be $camelCase
    $services->set(ValidVariableNameSniff::class);
    // add all @param, @return and @var annotations, in FQN
    $services->set(MissingParamAnnotationsFixer::class);
    $services->set(MissingReturnAnnotationFixer::class);
    $services->set(OrderedParamAnnotationsFixer::class);
    $services->set(FullyQualifiedClassNameInAnnotationSniff::class);

    // Custom Shopsys standards
    $services->set(EmptyStatementSniff::class);
    $services->set(ForLoopShouldBeWhileLoopSniff::class);
    $services->set(ForLoopWithTestFunctionCallSniff::class);
    $services->set(JumbledIncrementerSniff::class);
    $services->set(UnconditionalIfStatementSniff::class);
    $services->set(TodoSniff::class);
    $services->set(FixmeSniff::class);
    $services->set(NoSpaceAfterCastSniff::class);
    $services->set(CallTimePassByReferenceSniff::class);
    $services->set(CyclomaticComplexitySniff::class)
        ->property('absoluteComplexity', 13);
    $services->set(ConstructorNameSniff::class);
    $services->set(CamelCapsFunctionNameSniff::class);
    $services->set(DiscourageGotoSniff::class);
    $services->set(NoSilencedErrorsSniff::class);
    $services->set(GetRequestDataSniff::class);
    $services->set(InlineCommentSniff::class);
    $services->set(ValidClassNameSniff::class);
    $services->set(CamelCapsMethodNameSniff::class);
    $services->set(PhpCsValidVariableNameSniff::class);
    $services->set(DisallowMultipleAssignmentsSniff::class);
    $services->set(DisallowSizeFunctionsInLoopsSniff::class);
    $services->set(EvalSniff::class);
    $services->set(GlobalKeywordSniff::class);
    $services->set(InnerFunctionsSniff::class);
    $services->set(LowercasePHPFunctionsSniff::class);
    $services->set(NonExecutableCodeSniff::class);
    $services->set(StaticThisUsageSniff::class);
    $services->set(DoubleQuoteUsageSniff::class);
    $services->set(CastSpacingSniff::class);
    $services->set(LanguageConstructSpacingSniff::class);
    $services->set(LogicalOperatorSpacingSniff::class);
    $services->set(ArraySyntaxFixer::class)
        ->call('configure', [['syntax' => 'short']]);
    $services->set(CombineConsecutiveUnsetsFixer::class);
    $services->set(EregToPregFixer::class);
    $services->set(FunctionTypehintSpaceFixer::class);
    $services->set(IncludeFixer::class);
    $services->set(YodaStyleFixer::class)
        ->call('configure', [['equal' => false, 'identical' => false]]);
    $services->set(LinebreakAfterOpeningTagFixer::class);
    $services->set(NativeFunctionCasingFixer::class);
    $services->set(NoAliasFunctionsFixer::class);
    $services->set(NoBlankLinesAfterPhpdocFixer::class);
    $services->set(NoEmptyCommentFixer::class);
    $services->set(NoEmptyPhpdocFixer::class);
    $services->set(NoEmptyStatementFixer::class);
    $services->set(NoExtraConsecutiveBlankLinesFixer::class);
    $services->set(NoLeadingNamespaceWhitespaceFixer::class);
    $services->set(NoMixedEchoPrintFixer::class);
    $services->set(NoMultilineWhitespaceAroundDoubleArrowFixer::class);
    $services->set(NoMultilineWhitespaceBeforeSemicolonsFixer::class);
    $services->set(NoPhp4ConstructorFixer::class);
    $services->set(NoShortBoolCastFixer::class);
    $services->set(NoSpacesAroundOffsetFixer::class);
    $services->set(NoTrailingCommaInListCallFixer::class);
    $services->set(NoTrailingCommaInSinglelineArrayFixer::class);
    $services->set(NoUnneededControlParenthesesFixer::class);
    $services->set(NoUnusedImportsFixer::class);
    $services->set(NoUselessReturnFixer::class);
    $services->set(NoWhitespaceInBlankLineFixer::class);
    $services->set(NonPrintableCharacterFixer::class);
    $services->set(NormalizeIndexBraceFixer::class);
    $services->set(ObjectOperatorWithoutWhitespaceFixer::class);
    $services->set(OrderedImportsFixer::class)
        ->call('configure', [['sort_algorithm' => 'alpha', 'imports_order' => ['class', 'function', 'const']]]);
    $services->set(PhpdocAnnotationWithoutDotFixer::class);
    $services->set(PhpdocIndentFixer::class);
    $services->set(PhpdocNoUselessInheritdocFixer::class);
    $services->set(PhpdocNoAliasTagFixer::class)
        ->call('configure', [['type' => 'var']]);
    $services->set(PhpdocNoEmptyReturnFixer::class);
    $services->set(PhpdocNoAccessFixer::class);
    $services->set(PhpdocNoPackageFixer::class);
    $services->set(PhpdocOrderFixer::class);
    $services->set(PhpdocScalarFixer::class);
    $services->set(PhpdocSingleLineVarSpacingFixer::class);
    $services->set(PhpdocTrimFixer::class);
    $services->set(PhpdocVarWithoutNameFixer::class);
    $services->set(ProtectedToPrivateFixer::class);
    $services->set(SelfAccessorFixer::class);
    $services->set(SemicolonAfterInstructionFixer::class);
    $services->set(SingleBlankLineBeforeNamespaceFixer::class);
    $services->set(SpaceAfterSemicolonFixer::class);
    $services->set(SingleQuoteFixer::class);
    $services->set(SingleLineCommentStyleFixer::class)
        ->call('configure', [['comment_types' => ['hash']]]);
    $services->set(StandardizeNotEqualsFixer::class);
    $services->set(StrictParamFixer::class);
    $services->set(TrailingCommaInMultilineArrayFixer::class);
    $services->set(TrimArraySpacesFixer::class);
    // keep 1 empty line between constants, properties and methods
    // keep 0 empty lines after class open bracket {
    // keep 0 empty lines before class end bracket }
    $services->set(ClassAttributesSeparationFixer::class)
        ->call('configure', [['elements' => ['property', 'method']]]);

    // Code Metrics
    $services->set(ClassTraitAndInterfaceLengthSniff::class)
        ->property('maxLength', 550);
    $services->set(FunctionLengthSniff::class)
        ->property('maxLength', 60);
    $services->set(PropertyPerClassLimitSniff::class)
        ->property('maxCount', 30);
};
