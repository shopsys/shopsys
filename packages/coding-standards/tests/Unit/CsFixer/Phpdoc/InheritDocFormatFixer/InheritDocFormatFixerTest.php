<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\InheritDocFormatFixer;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopsys\CodingStandards\CsFixer\Phpdoc\InheritDocFormatFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

#[CoversClass(InheritDocFormatFixer::class)]
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
    public static function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.php', __DIR__ . '/wrong/wrong.php'];
    }
}
