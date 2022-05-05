<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\UniqIdPrefixAndEntropyFixer;

use Shopsys\CodingStandards\CsFixer\UniqIdPrefixAndEntropyFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class UniqIdPrefixAndEntropyFixerTest extends AbstractFixerTestCase
{
    /**
     * @return \Shopsys\CodingStandards\CsFixer\UniqIdPrefixAndEntropyFixer
     */
    protected function createFixerService(): UniqIdPrefixAndEntropyFixer
    {
        return new UniqIdPrefixAndEntropyFixer();
    }

    /**
     * {@inheritDoc}
     */
    public function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.php', __DIR__ . '/wrong/wrong.php'];
        yield [__DIR__ . '/correct/correct.php'];
    }
}
