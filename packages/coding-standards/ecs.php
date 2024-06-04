<?php

declare(strict_types=1);

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
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;
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
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use Shopsys\CodingStandards\CsFixer\ForbiddenDumpFixer;
use Shopsys\CodingStandards\CsFixer\MissingButtonTypeFixer;
use Shopsys\CodingStandards\CsFixer\OrmJoinColumnRequireNullableFixer;
use Shopsys\CodingStandards\CsFixer\Phpdoc\InheritDocFormatFixer;
use Shopsys\CodingStandards\CsFixer\Phpdoc\MissingParamAnnotationsFixer;
use Shopsys\CodingStandards\CsFixer\Phpdoc\MissingReturnAnnotationFixer;
use Shopsys\CodingStandards\CsFixer\Phpdoc\OrderedParamAnnotationsFixer;
use Shopsys\CodingStandards\Helper\CyclomaticComplexitySniffSetting;
use Shopsys\CodingStandards\Sniffs\ForbiddenDoctrineDefaultValueSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenDoctrineInheritanceSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenDumpSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenExitSniff;
use Shopsys\CodingStandards\Sniffs\ForbiddenSuperGlobalSniff;
use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Shopsys\CodingStandards\Sniffs\ValidVariableNameSniff;
use SlevomatCodingStandard\Sniffs\Arrays\TrailingArrayCommaSniff;
use SlevomatCodingStandard\Sniffs\Classes\ClassLengthSniff;
use SlevomatCodingStandard\Sniffs\Classes\ParentCallSpacingSniff;
use SlevomatCodingStandard\Sniffs\Classes\RequireConstructorPropertyPromotionSniff;
use SlevomatCodingStandard\Sniffs\Classes\RequireMultiLineMethodSignatureSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DisallowCommentAfterCodeSniff;
use SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff;
use SlevomatCodingStandard\Sniffs\Commenting\InlineDocCommentDeclarationSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\EarlyExitSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\UselessIfConditionWithReturnSniff;
use SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff;
use SlevomatCodingStandard\Sniffs\Functions\RequireTrailingCommaInCallSniff;
use SlevomatCodingStandard\Sniffs\Functions\RequireTrailingCommaInClosureUseSniff;
use SlevomatCodingStandard\Sniffs\Functions\RequireTrailingCommaInDeclarationSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\FullyQualifiedClassNameInAnnotationSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Operators\DisallowEqualOperatorsSniff;
use SlevomatCodingStandard\Sniffs\PHP\ForbiddenClassesSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DeclareStrictTypesSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\NullableTypeForNullDefaultValueSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSpacingSniff;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withParallel()
    ->withSpacing(indentation: Option::INDENTATION_SPACES, lineEnding: PHP_EOL)
    ->withSets([
        SetList::PSR_12,
        SetList::CLEAN_CODE,
        SetList::ARRAY,
        SetList::COMMENTS,
        SetList::CONTROL_STRUCTURES,
        SetList::DOCBLOCK,
        SetList::NAMESPACES,
    ])
    ->withRules([
        InlineDocCommentDeclarationSniff::class,
        NullableTypeForNullDefaultValueSniff::class,
        ReturnTypeHintSpacingSniff::class,
    ])
    ->withConfiguredRule(
        ForbiddenClassesSniff::class, [
        'forbiddenClasses' => [
            'Overblog\GraphQLBundle\Error\UserError' => null,
            'GraphQL\Error\UserError' => null,
        ],
    ])
    ->withRules([
        InheritDocFormatFixer::class,
        ForbiddenDumpFixer::class,
        MissingButtonTypeFixer::class,
        OrmJoinColumnRequireNullableFixer::class,
        ObjectIsCreatedByFactorySniff::class,
        ForbiddenDumpSniff::class,
        ForbiddenExitSniff::class,
        ForbiddenSuperGlobalSniff::class,
        ForbiddenDoctrineInheritanceSniff::class,
        ForbiddenDoctrineDefaultValueSniff::class,
        // method arguments and variables should be $camelCase
        ValidVariableNameSniff::class,
        // add all @param, @return and @var annotations, in FQN
        MissingParamAnnotationsFixer::class,
        MissingReturnAnnotationFixer::class,
        OrderedParamAnnotationsFixer::class,
        FullyQualifiedClassNameInAnnotationSniff::class,
        EmptyStatementSniff::class,
        ForLoopShouldBeWhileLoopSniff::class,
        ForLoopWithTestFunctionCallSniff::class,
        JumbledIncrementerSniff::class,
        UnconditionalIfStatementSniff::class,
        TodoSniff::class,
        FixmeSniff::class,
        NoSpaceAfterCastSniff::class,
        CallTimePassByReferenceSniff::class,
        CamelCapsFunctionNameSniff::class,
        DiscourageGotoSniff::class,
        NoSilencedErrorsSniff::class,
        GetRequestDataSniff::class,
        InlineCommentSniff::class,
        ValidClassNameSniff::class,
        CamelCapsMethodNameSniff::class,
        PhpCsValidVariableNameSniff::class,
        DisallowMultipleAssignmentsSniff::class,
        DisallowSizeFunctionsInLoopsSniff::class,
        EvalSniff::class,
        GlobalKeywordSniff::class,
        InnerFunctionsSniff::class,
        LowercasePHPFunctionsSniff::class,
        NonExecutableCodeSniff::class,
        StaticThisUsageSniff::class,
        DoubleQuoteUsageSniff::class,
        CastSpacingSniff::class,
        LanguageConstructSpacingSniff::class,
        LogicalOperatorSpacingSniff::class,
        CombineConsecutiveUnsetsFixer::class,
        EregToPregFixer::class,
        IncludeFixer::class,
        LinebreakAfterOpeningTagFixer::class,
        NativeFunctionCasingFixer::class,
        NoAliasFunctionsFixer::class,
        NoBlankLinesAfterPhpdocFixer::class,
        NoEmptyCommentFixer::class,
        NoEmptyPhpdocFixer::class,
        NoEmptyStatementFixer::class,
        NoLeadingNamespaceWhitespaceFixer::class,
        NoMixedEchoPrintFixer::class,
        NoMultilineWhitespaceAroundDoubleArrowFixer::class,
        NoPhp4ConstructorFixer::class,
        NoShortBoolCastFixer::class,
        NoSpacesAroundOffsetFixer::class,
        NoUnneededControlParenthesesFixer::class,
        NoUnusedImportsFixer::class,
        NoUselessReturnFixer::class,
        NoWhitespaceInBlankLineFixer::class,
        NonPrintableCharacterFixer::class,
        NormalizeIndexBraceFixer::class,
        ObjectOperatorWithoutWhitespaceFixer::class,
        PhpdocAnnotationWithoutDotFixer::class,
        PhpdocIndentFixer::class,
        PhpdocNoUselessInheritdocFixer::class,
        PhpdocNoAliasTagFixer::class,
        PhpdocNoEmptyReturnFixer::class,
        PhpdocNoAccessFixer::class,
        PhpdocNoPackageFixer::class,
        PhpdocOrderFixer::class,
        PhpdocScalarFixer::class,
        PhpdocSingleLineVarSpacingFixer::class,
        PhpdocTrimFixer::class,
        ProtectedToPrivateFixer::class,
        SelfAccessorFixer::class,
        SemicolonAfterInstructionFixer::class,
        SpaceAfterSemicolonFixer::class,
        SingleQuoteFixer::class,
        StandardizeNotEqualsFixer::class,
        StrictParamFixer::class,
        TrimArraySpacesFixer::class,
        RequireTrailingCommaInDeclarationSniff::class,
        RequireTrailingCommaInClosureUseSniff::class,
        RequireTrailingCommaInCallSniff::class,
        TrailingArrayCommaSniff::class,
        RequireConstructorPropertyPromotionSniff::class,
        PropertyTypeHintSniff::class,
        DisallowEqualOperatorsSniff::class,
        NoUselessElseFixer::class,
        AssignmentInConditionSniff::class,
        DisallowEmptySniff::class,
        ParentCallSpacingSniff::class,
        UselessIfConditionWithReturnSniff::class,
    ])
    ->withConfiguredRule(CyclomaticComplexitySniff::class, [
        'absoluteComplexity' => CyclomaticComplexitySniffSetting::DEFAULT_ABSOLUTE_COMPLEXITY,
    ])
    ->withConfiguredRule(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ])
    ->withConfiguredRule(FunctionDeclarationArgumentSpacingSniff::class, [
        'equalsSpacing' => 1,
    ])
    ->withConfiguredRule(YodaStyleFixer::class, [
        'equal' => false,
        'identical' => false,
    ])
    ->withConfiguredRule(OrderedImportsFixer::class, [
        'sort_algorithm' => 'alpha',
        'imports_order' => [
            'class',
            'function',
            'const',
        ],
    ])
    ->withConfiguredRule(SingleLineCommentStyleFixer::class, [
        'comment_types' => ['hash'],
    ])
    // keep 1 empty line between constants, properties and methods
    // keep 0 empty lines after class open bracket {
    // keep 0 empty lines before class end bracket }
    ->withConfiguredRule(ClassAttributesSeparationFixer::class, [
        'elements' => [
            'property' => ClassAttributesSeparationFixer::SPACING_ONE,
            'method' => ClassAttributesSeparationFixer::SPACING_ONE,
        ],
    ])
    ->withConfiguredRule(BlankLineBeforeStatementFixer::class, [
        'statements' => [
            'break',
            'continue',
            'declare',
            'do',
            'for',
            'foreach',
            'if',
            'return',
            'switch',
            'throw',
            'try',
            'while',
            'yield',
        ],
    ])
    ->withConfiguredRule(RequireMultiLineMethodSignatureSniff::class, [
        'minLineLength' => 120,
    ])
    ->withConfiguredRule(DeclareStrictTypesSniff::class, [
        'spacesCountAroundEqualsSign' => 0,
    ])
    ->withConfiguredRule(EarlyExitSniff::class, [
        'ignoreStandaloneIfInScope' => true,
        'ignoreOneLineTrailingIf' => true,
        'ignoreTrailingIfWithOneInstruction' => true,
    ])
    ->withConfiguredRule(ReferenceUsedNamesOnlySniff::class, [
        'allowPartialUses' => true,
    ])
    ->withConfiguredRule(DocCommentSpacingSniff::class, [
        'linesCountBetweenDifferentAnnotationsTypes' => 0,
    ])
    ->withConfiguredRule(ClassLengthSniff::class, [
        'maxLinesLength' => 550,
    ])
    ->withConfiguredRule(FunctionLengthSniff::class, [
        'maxLinesLength' => 60,
    ])
    ->withSkip([
        // private properties should not start with "_"
        PhpCsValidVariableNameSniff::class . '.PrivateNoUnderscore',
        // it is not necessary to have blank line after control structure
        ControlStructureSpacingSniff::class . '.NoLineAfterClose',
        // allow empty "catch (Exception $exception) { }"
        EmptyStatementSniff::class . '.DetectedCatch',
        FunctionCallSignatureSniff::class . '.Indent',
        ArrayOpenerAndCloserNewlineFixer::class,
        ArrayListItemNewlineFixer::class,
        DisallowCommentAfterCodeSniff::class . '.DisallowedCommentAfterCode',
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
