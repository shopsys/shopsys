<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Shopsys\CodingStandards\SetList\SetList as ShopsysSetList;
use SlevomatCodingStandard\Sniffs\Commenting\DeprecatedAnnotationDeclarationSniff;
use Sniffer\ExtendedApiClassNamespaceSniffer;
use Sniffer\FrontendApiNamespaceSniffer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSets([
        ShopsysSetList::SHOPSYS_CODING_STANDARD,
    ])
    ->withRules([
        DeclareStrictTypesFixer::class,
        DeprecatedAnnotationDeclarationSniff::class,
        FrontendApiNamespaceSniffer::class,
        ExtendedApiClassNamespaceSniffer::class,
        PhpdocToPropertyTypeFixer::class,
    ])
    ->withConfiguredRule(CyclomaticComplexitySniff::class, [
        'absoluteComplexity' => 19,
    ])
    ->withSkip(include __DIR__ . '/ecs-skip-rules.php');
