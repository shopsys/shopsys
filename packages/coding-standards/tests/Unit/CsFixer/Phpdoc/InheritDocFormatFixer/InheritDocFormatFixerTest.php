<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\InheritDocFormatFixer;

use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Shopsys\CodingStandards\CsFixer\Phpdoc\InheritDocFormatFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

#[CoversClass(InheritDocFormatFixer::class)]
final class InheritDocFormatFixerTest extends AbstractFixerTestCase
{
    #[Override]
    protected function createFixerService(): InheritDocFormatFixer
    {
        return new InheritDocFormatFixer();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.php', __DIR__ . '/wrong/wrong.php'];
    }
}
