<?php

declare(strict_types=1);

use ObjectCalisthenics\Sniffs\Files\ClassTraitAndInterfaceLengthSniff;
use ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff;
use ObjectCalisthenics\Sniffs\Metrics\PropertyPerClassLimitSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
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
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Functions\FunctionDeclarationArgumentSpacingSniff;
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
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\Basic\NonPrintableCharacterFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\NoPhp4ConstructorFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;
use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoTrailingCommaInListCallFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
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
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Semicolon\SpaceAfterSemicolonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
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
use SlevomatCodingStandard\Sniffs\Classes\ParentCallSpacingSniff;
use SlevomatCodingStandard\Sniffs\Classes\UnusedPrivateElementsSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DisallowCommentAfterCodeSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff;
use SlevomatCodingStandard\Sniffs\Commenting\InlineDocCommentDeclarationSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\EarlyExitSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\UselessIfConditionWithReturnSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\FullyQualifiedClassNameInAnnotationSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Operators\DisallowEqualOperatorsSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\NullableTypeForNullDefaultValueSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSpacingSniff;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\IndentDetector;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

/**
 * @param Symplify\EasyCodingStandard\Config\ECSConfig $ecsConfig
 */
return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->parallel();
    $ecsConfig->lineEnding('\n');

    $ecsConfig->sets([
        SetList::PSR_12,
        SetList::CLEAN_CODE,
        SetList::ARRAY,
        SetList::COMMENTS,
        SetList::CONTROL_STRUCTURES,
        SetList::DOCBLOCK,
        SetList::NAMESPACES,
    ]);

    // helper services
    $services = $ecsConfig->services();
    $services->defaults()->autowire();
    $services->set(FunctionsAnalyzer::class);
    $services->set(PhpToDocTypeTransformer::class);
    $services->set(FqnNameResolver::class);
    $services->set(NamespaceUsesAnalyzer::class);
    $services->set(IndentDetector::class);
    $services->set(FileFinder::class);

    // Slevomat Checkers
    $ecsConfig->rule(UnusedPrivateElementsSniff::class);
    $ecsConfig->rule(InlineDocCommentDeclarationSniff::class);
    $ecsConfig->rule(NullableTypeForNullDefaultValueSniff::class);
    $ecsConfig->rule(ReturnTypeHintSpacingSniff::class);

    // Shopsys Checkers
    $ecsConfig->rule(ForbiddenDumpFixer::class);
    $ecsConfig->rule(MissingButtonTypeFixer::class);
    $ecsConfig->rule(OrmJoinColumnRequireNullableFixer::class);
    $ecsConfig->rule(RedundantMarkDownTrailingSpacesFixer::class);
    $ecsConfig->rule(ObjectIsCreatedByFactorySniff::class);
    $ecsConfig->rule(ForbiddenDumpSniff::class);
    $ecsConfig->rule(ForbiddenExitSniff::class);
    $ecsConfig->rule(ForbiddenSuperGlobalSniff::class);
    $ecsConfig->rule(ForbiddenDoctrineInheritanceSniff::class);
    $ecsConfig->rule(ForbiddenDoctrineDefaultValueSniff::class);
    // method arguments and variables should be $camelCase
    $ecsConfig->rule(ValidVariableNameSniff::class);
    // add all @param, @return and @var annotations, in FQN
    $ecsConfig->rule(MissingParamAnnotationsFixer::class);
    $ecsConfig->rule(MissingReturnAnnotationFixer::class);
    $ecsConfig->rule(OrderedParamAnnotationsFixer::class);
    $ecsConfig->rule(FullyQualifiedClassNameInAnnotationSniff::class);

    // Custom Shopsys standards
    $ecsConfig->rule(EmptyStatementSniff::class);
    $ecsConfig->rule(ForLoopShouldBeWhileLoopSniff::class);
    $ecsConfig->rule(ForLoopWithTestFunctionCallSniff::class);
    $ecsConfig->rule(JumbledIncrementerSniff::class);
    $ecsConfig->rule(UnconditionalIfStatementSniff::class);
    $ecsConfig->rule(TodoSniff::class);
    $ecsConfig->rule(FixmeSniff::class);
    $ecsConfig->rule(NoSpaceAfterCastSniff::class);
    $ecsConfig->rule(CallTimePassByReferenceSniff::class);
    $ecsConfig->ruleWithConfiguration(CyclomaticComplexitySniff::class, [
        'absoluteComplexity' => 13,
    ]);
    $ecsConfig->rule(ConstructorNameSniff::class);
    $ecsConfig->rule(CamelCapsFunctionNameSniff::class);
    $ecsConfig->rule(DiscourageGotoSniff::class);
    $ecsConfig->rule(NoSilencedErrorsSniff::class);
    $ecsConfig->rule(GetRequestDataSniff::class);
    $ecsConfig->rule(InlineCommentSniff::class);
    $ecsConfig->rule(ValidClassNameSniff::class);
    $ecsConfig->rule(CamelCapsMethodNameSniff::class);
    $ecsConfig->rule(PhpCsValidVariableNameSniff::class);
    $ecsConfig->rule(DisallowMultipleAssignmentsSniff::class);
    $ecsConfig->rule(DisallowSizeFunctionsInLoopsSniff::class);
    $ecsConfig->rule(EvalSniff::class);
    $ecsConfig->rule(GlobalKeywordSniff::class);
    $ecsConfig->rule(InnerFunctionsSniff::class);
    $ecsConfig->rule(LowercasePHPFunctionsSniff::class);
    $ecsConfig->rule(NonExecutableCodeSniff::class);
    $ecsConfig->rule(StaticThisUsageSniff::class);
    $ecsConfig->rule(DoubleQuoteUsageSniff::class);
    $ecsConfig->rule(CastSpacingSniff::class);
    $ecsConfig->rule(LanguageConstructSpacingSniff::class);
    $ecsConfig->rule(LogicalOperatorSpacingSniff::class);
    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);
    $ecsConfig->rule(CombineConsecutiveUnsetsFixer::class);
    $ecsConfig->rule(EregToPregFixer::class);
    $ecsConfig->ruleWithConfiguration(FunctionDeclarationArgumentSpacingSniff::class, [
        'equalsSpacing' => 1,
    ]);
    $ecsConfig->rule(IncludeFixer::class);
    $ecsConfig->ruleWithConfiguration(YodaStyleFixer::class, [
        'equal' => false,
        'identical' => false,
    ]);
    $ecsConfig->rule(LinebreakAfterOpeningTagFixer::class);
    $ecsConfig->rule(NativeFunctionCasingFixer::class);
    $ecsConfig->rule(NoAliasFunctionsFixer::class);
    $ecsConfig->rule(NoBlankLinesAfterPhpdocFixer::class);
    $ecsConfig->rule(NoEmptyCommentFixer::class);
    $ecsConfig->rule(NoEmptyPhpdocFixer::class);
    $ecsConfig->rule(NoEmptyStatementFixer::class);
    $ecsConfig->rule(NoLeadingNamespaceWhitespaceFixer::class);
    $ecsConfig->rule(NoMixedEchoPrintFixer::class);
    $ecsConfig->rule(NoMultilineWhitespaceAroundDoubleArrowFixer::class);
    $ecsConfig->rule(NoPhp4ConstructorFixer::class);
    $ecsConfig->rule(NoShortBoolCastFixer::class);
    $ecsConfig->rule(NoSpacesAroundOffsetFixer::class);
    $ecsConfig->rule(NoTrailingCommaInListCallFixer::class);
    $ecsConfig->rule(NoTrailingCommaInSinglelineArrayFixer::class);
    $ecsConfig->rule(NoUnneededControlParenthesesFixer::class);
    $ecsConfig->rule(NoUnusedImportsFixer::class);
    $ecsConfig->rule(NoUselessReturnFixer::class);
    $ecsConfig->rule(NoWhitespaceInBlankLineFixer::class);
    $ecsConfig->rule(NonPrintableCharacterFixer::class);
    $ecsConfig->rule(NormalizeIndexBraceFixer::class);
    $ecsConfig->rule(ObjectOperatorWithoutWhitespaceFixer::class);
    $ecsConfig->ruleWithConfiguration(OrderedImportsFixer::class, [
        'sort_algorithm' => 'alpha',
        'imports_order' => [
            'class',
            'function',
            'const',
        ],
    ]);
    $ecsConfig->rule(PhpdocAnnotationWithoutDotFixer::class);
    $ecsConfig->rule(PhpdocIndentFixer::class);
    $ecsConfig->rule(PhpdocNoUselessInheritdocFixer::class);
    $ecsConfig->rule(PhpdocNoAliasTagFixer::class);
    $ecsConfig->rule(PhpdocNoEmptyReturnFixer::class);
    $ecsConfig->rule(PhpdocNoAccessFixer::class);
    $ecsConfig->rule(PhpdocNoPackageFixer::class);
    $ecsConfig->rule(PhpdocOrderFixer::class);
    $ecsConfig->rule(PhpdocScalarFixer::class);
    $ecsConfig->rule(PhpdocSingleLineVarSpacingFixer::class);
    $ecsConfig->rule(PhpdocTrimFixer::class);
    $ecsConfig->rule(PhpdocVarWithoutNameFixer::class);
    $ecsConfig->rule(ProtectedToPrivateFixer::class);
    $ecsConfig->rule(SelfAccessorFixer::class);
    $ecsConfig->rule(SemicolonAfterInstructionFixer::class);
    $ecsConfig->rule(SingleBlankLineBeforeNamespaceFixer::class);
    $ecsConfig->rule(SpaceAfterSemicolonFixer::class);
    $ecsConfig->rule(SingleQuoteFixer::class);
    $ecsConfig->ruleWithConfiguration(SingleLineCommentStyleFixer::class, [
        'comment_types' => ['hash'],
    ]);
    $ecsConfig->rule(StandardizeNotEqualsFixer::class);
    $ecsConfig->rule(StrictParamFixer::class);
    $ecsConfig->rule(TrimArraySpacesFixer::class);
    // keep 1 empty line between constants, properties and methods
    // keep 0 empty lines after class open bracket {
    // keep 0 empty lines before class end bracket }
    $ecsConfig->ruleWithConfiguration(ClassAttributesSeparationFixer::class, [
        'elements' => [
            'property' => ClassAttributesSeparationFixer::SPACING_ONE,
            'method' => ClassAttributesSeparationFixer::SPACING_ONE,
        ],
    ]);
    $ecsConfig->rule(DisallowEqualOperatorsSniff::class);
    $ecsConfig->rule(ValidClassNameSniff::class);
    $ecsConfig->rule(NoUselessElseFixer::class);
    $ecsConfig->rule(AssignmentInConditionSniff::class);
    $ecsConfig->rule(DisallowEmptySniff::class);
    $ecsConfig->ruleWithConfiguration(EarlyExitSniff::class, [
        'ignoreStandaloneIfInScope' => true,
        'ignoreOneLineTrailingIf' => true,
        'ignoreTrailingIfWithOneInstruction' => true,
    ]);
    $ecsConfig->ruleWithConfiguration(ParentCallSpacingSniff::class, [
        'linesCountBeforeParentCall' => 1,
        'linesCountAfterParentCall' => 1,
    ]);
    $ecsConfig->ruleWithConfiguration(ReferenceUsedNamesOnlySniff::class, [
        'allowPartialUses' => true,
    ]);
    $ecsConfig->ruleWithConfiguration(DocCommentSpacingSniff::class, [
        'linesCountBetweenDifferentAnnotationsTypes' => 0,
    ]);
    $ecsConfig->rule(UselessIfConditionWithReturnSniff::class);

    // Code Metrics
    $ecsConfig->ruleWithConfiguration(ClassTraitAndInterfaceLengthSniff::class, [
        'maxLength' => 550,
    ]);
    $ecsConfig->ruleWithConfiguration(FunctionLengthSniff::class, [
        'maxLength' => 60,
    ]);
    $ecsConfig->ruleWithConfiguration(PropertyPerClassLimitSniff::class, [
        'maxCount' => 30,
    ]);

    $ecsConfig->skip([
        __DIR__ . '/tests/Unit/**/wrong/*',
        __DIR__ . '/tests/Unit/**/Wrong/*',
        __DIR__ . '/tests/Unit/**/correct/*',
        __DIR__ . '/tests/Unit/**/Correct/*',
        __DIR__ . '/tests/Unit/**/fixed/*',
        // private properties should not start with "_"
        PhpCsValidVariableNameSniff::class . '.PrivateNoUnderscore' => null,
        // it is not necessary to have blank line after control structure
        ControlStructureSpacingSniff::class . '.NoLineAfterClose' => null,
        // allow empty "catch (Exception $exception) { }"
        EmptyStatementSniff::class . '.DetectedCatch' => null,
        FunctionCallSignatureSniff::class . '.Indent' => null,
        ArrayOpenerAndCloserNewlineFixer::class => null,
        ArrayListItemNewlineFixer::class => null,
        DisallowCommentAfterCodeSniff::class . '.DisallowedCommentAfterCode' => null,
        // rule is applied via `clean-code` set, but we do not want to use it for now
        // some variables exist just because of the right annotation
        ReturnAssignmentFixer::class => null,
        // rule is applied via `control-structures` set, but we do not want to use it for now
        OrderedClassElementsFixer::class => null,
        // rule is applied via `docblock` set, but we do not want to use it for now
        // remove variable name from @var and @type annotations
        PhpdocVarWithoutNameFixer::class => null,
        // rule is applied via `docblock` set, but we do not want to use it for now
        // remove inheritdoc
        NoSuperfluousPhpdocTagsFixer::class => null,
        // rule breaks jms/translation-bundle as it fails on this usage: `[, $b] = $var`
        // won't do any changes after upgrade
        ListSyntaxFixer::class => null,
    ]);
};
