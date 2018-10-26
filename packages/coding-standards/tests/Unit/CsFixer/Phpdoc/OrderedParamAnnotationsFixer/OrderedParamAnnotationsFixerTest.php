<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\OrderedParamAnnotationsFixer;

use Iterator;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;

/**
 * @covers \Shopsys\CodingStandards\CsFixer\Phpdoc\OrderedParamAnnotationsFixer
 */
final class OrderedParamAnnotationsFixerTest extends AbstractCheckerTestCase
{
    /**
     * @dataProvider provideWrongToFixedFiles()
     */
    public function testFix(string $wrongFile, string $fixedFile): void
    {
        $this->doTestWrongToFixedFile($wrongFile, $fixedFile);
    }

    public function provideWrongToFixedFiles(): Iterator
    {
        yield [__DIR__ . '/wrong/wrong.php', __DIR__ . '/fixed/fixed.php'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
