<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\MissingParamAnnotationsFixer;

use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\WhitespacesFixerConfig;
use Shopsys\CodingStandards\CsFixer\Phpdoc\MissingParamAnnotationsFixer;
use Shopsys\CodingStandards\Helper\FqnNameResolver;
use Shopsys\CodingStandards\Helper\PhpToDocTypeTransformer;
use Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\IndentDetector;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

/**
 * @covers \Shopsys\CodingStandards\CsFixer\Phpdoc\MissingParamAnnotationsFixer
 */
final class MissingParamAnnotationsFixerTest extends AbstractFixerTestCase
{
    /**
     * @return \Shopsys\CodingStandards\CsFixer\Phpdoc\MissingParamAnnotationsFixer
     */
    protected function createFixerService(): MissingParamAnnotationsFixer
    {
        $namespaceUsesAnalyzer = new NamespaceUsesAnalyzer();
        $whitespacesFixerConfig = new WhitespacesFixerConfig();
        $functionsAnalyzer = new FunctionsAnalyzer();
        $fqnNameResolver = new FqnNameResolver($namespaceUsesAnalyzer);
        $phpToDocTypeTransformer = new PhpToDocTypeTransformer($fqnNameResolver);
        $indentDetector = new IndentDetector($whitespacesFixerConfig);

        return new MissingParamAnnotationsFixer(
            $whitespacesFixerConfig,
            $functionsAnalyzer,
            $phpToDocTypeTransformer,
            $indentDetector
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.php', __DIR__ . '/wrong/wrong.php'];

        yield [__DIR__ . '/fixed/fixed2.php', __DIR__ . '/wrong/wrong2.php'];

        yield [__DIR__ . '/fixed/fixed3.php', __DIR__ . '/wrong/wrong3.php'];

        yield [__DIR__ . '/fixed/fixed4.php', __DIR__ . '/wrong/wrong4.php'];

        yield [__DIR__ . '/fixed/fixed5.php', __DIR__ . '/wrong/wrong5.php'];

        yield [__DIR__ . '/fixed/fixed6.php', __DIR__ . '/wrong/wrong6.php'];

        yield [__DIR__ . '/correct/correct.php'];
    }
}
