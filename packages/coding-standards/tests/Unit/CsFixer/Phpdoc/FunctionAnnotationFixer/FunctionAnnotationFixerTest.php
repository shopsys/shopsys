<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\FunctionAnnotationFixer;

use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\WhitespacesFixerConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use Shopsys\CodingStandards\CsFixer\Phpdoc\MissingParamAnnotationsFixer;
use Shopsys\CodingStandards\CsFixer\Phpdoc\MissingReturnAnnotationFixer;
use Shopsys\CodingStandards\CsFixer\Phpdoc\OrderedParamAnnotationsFixer;
use Shopsys\CodingStandards\Helper\FqnNameResolver;
use Shopsys\CodingStandards\Helper\PhpToDocTypeTransformer;
use Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\IndentDetector;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;
use Tests\CodingStandards\Unit\CsFixer\ChainedFixer;

#[CoversClass(MissingParamAnnotationsFixer::class)]
#[CoversClass(MissingReturnAnnotationFixer::class)]
#[CoversClass(OrderedParamAnnotationsFixer::class)]
final class FunctionAnnotationFixerTest extends AbstractFixerTestCase
{
    /**
     * @return \Tests\CodingStandards\Unit\CsFixer\ChainedFixer
     */
    protected function createFixerService(): ChainedFixer
    {
        $fixer = new ChainedFixer();

        $namespaceUsesAnalyzer = new NamespaceUsesAnalyzer();
        $whitespacesFixerConfig = new WhitespacesFixerConfig();
        $functionsAnalyzer = new FunctionsAnalyzer();
        $fqnNameResolver = new FqnNameResolver($namespaceUsesAnalyzer);
        $phpToDocTypeTransformer = new PhpToDocTypeTransformer($fqnNameResolver);
        $indentDetector = new IndentDetector($whitespacesFixerConfig);

        $fixer->registerFixer(
            new MissingParamAnnotationsFixer(
                $whitespacesFixerConfig,
                $functionsAnalyzer,
                $phpToDocTypeTransformer,
                $indentDetector,
            ),
        );

        $fixer->registerFixer(
            new MissingReturnAnnotationFixer(
                $whitespacesFixerConfig,
                $functionsAnalyzer,
                $phpToDocTypeTransformer,
                $indentDetector,
            ),
        );

        $fixer->registerFixer(
            new OrderedParamAnnotationsFixer($functionsAnalyzer),
        );

        return $fixer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.php', __DIR__ . '/wrong/wrong.php'];

        yield [__DIR__ . '/fixed/fixed2.php', __DIR__ . '/wrong/wrong2.php'];

        yield [__DIR__ . '/fixed/fixed3.php', __DIR__ . '/wrong/wrong3.php'];

        yield [__DIR__ . '/correct/correct.php'];
    }
}
