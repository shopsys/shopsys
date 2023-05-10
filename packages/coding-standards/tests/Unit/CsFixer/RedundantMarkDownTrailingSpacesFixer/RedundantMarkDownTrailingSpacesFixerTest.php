<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\RedundantMarkDownTrailingSpacesFixer;

use Shopsys\CodingStandards\CsFixer\RedundantMarkDownTrailingSpacesFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class RedundantMarkDownTrailingSpacesFixerTest extends AbstractFixerTestCase
{
    /**
     * @return \Shopsys\CodingStandards\CsFixer\RedundantMarkDownTrailingSpacesFixer
     */
    protected function createFixerService(): RedundantMarkDownTrailingSpacesFixer
    {
        return new RedundantMarkDownTrailingSpacesFixer();
    }

    /**
     * {@inheritdoc}
     */
    public function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.md', __DIR__ . '/wrong/wrong.md'];
        yield [__DIR__ . '/correct/correct.md'];
    }
}
