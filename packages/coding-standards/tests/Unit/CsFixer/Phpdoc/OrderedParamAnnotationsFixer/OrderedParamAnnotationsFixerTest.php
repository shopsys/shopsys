<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\OrderedParamAnnotationsFixer;

use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use Shopsys\CodingStandards\CsFixer\Phpdoc\OrderedParamAnnotationsFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

/**
 * @covers \Shopsys\CodingStandards\CsFixer\Phpdoc\OrderedParamAnnotationsFixer
 */
final class OrderedParamAnnotationsFixerTest extends AbstractFixerTestCase
{
    /**
     * @return \Shopsys\CodingStandards\CsFixer\Phpdoc\OrderedParamAnnotationsFixer
     */
    protected function createFixerService(): OrderedParamAnnotationsFixer
    {
        $functionsAnalyzer = new FunctionsAnalyzer();

        return new OrderedParamAnnotationsFixer($functionsAnalyzer);
    }

    /**
     * {@inheritDoc}
     */
    public function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.php', __DIR__ . '/wrong/wrong.php'];

        yield [__DIR__ . '/correct/correct.php'];

        yield [__DIR__ . '/correct/correct2.php'];

        yield [__DIR__ . '/correct/correct3.php'];

        yield [__DIR__ . '/correct/correct4.php'];
    }
}
