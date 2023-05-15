<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\MissingReturnAnnotationFixer;

use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\WhitespacesFixerConfig;
use Shopsys\CodingStandards\CsFixer\Phpdoc\MissingReturnAnnotationFixer;
use Shopsys\CodingStandards\Helper\FqnNameResolver;
use Shopsys\CodingStandards\Helper\PhpToDocTypeTransformer;
use Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\IndentDetector;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

/**
 * @covers \Shopsys\CodingStandards\CsFixer\Phpdoc\MissingReturnAnnotationFixer
 */
final class MissingReturnAnnotationFixerTest extends AbstractFixerTestCase
{
    /**
     * @return \Shopsys\CodingStandards\CsFixer\Phpdoc\MissingReturnAnnotationFixer
     */
    protected function createFixerService(): MissingReturnAnnotationFixer
    {
        $namespaceUsesAnalyzer = new NamespaceUsesAnalyzer();
        $whitespacesFixerConfig = new WhitespacesFixerConfig();
        $functionsAnalyzer = new FunctionsAnalyzer();
        $fqnNameResolver = new FqnNameResolver($namespaceUsesAnalyzer);
        $phpToDocTypeTransformer = new PhpToDocTypeTransformer($fqnNameResolver);
        $indentDetector = new IndentDetector($whitespacesFixerConfig);

        return new MissingReturnAnnotationFixer(
            $whitespacesFixerConfig,
            $functionsAnalyzer,
            $phpToDocTypeTransformer,
            $indentDetector,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.php', __DIR__ . '/wrong/wrong.php'];
        yield [__DIR__ . '/fixed/fixed2.php', __DIR__ . '/wrong/wrong2.php'];
        yield [__DIR__ . '/correct/correct.php'];
    }
}
