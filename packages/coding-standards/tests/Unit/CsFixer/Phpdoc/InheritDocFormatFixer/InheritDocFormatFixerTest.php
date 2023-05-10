<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\InheritDocFormatFixer;

use Shopsys\CodingStandards\CsFixer\Phpdoc\InheritDocFormatFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

/**
 * @covers \Shopsys\CodingStandards\CsFixer\Phpdoc\InheritDocFormatFixer
 */
final class InheritDocFormatFixerTest extends AbstractFixerTestCase
{
    /**
     * @return \Shopsys\CodingStandards\CsFixer\Phpdoc\InheritDocFormatFixer
     */
    protected function createFixerService(): InheritDocFormatFixer
    {
        return new InheritDocFormatFixer();
    }

    /**
     * {@inheritdoc}
     */
    public function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.php', __DIR__ . '/wrong/wrong.php'];
    }
}
