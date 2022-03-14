<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\MissingButtonTypeFixer;

use Shopsys\CodingStandards\CsFixer\MissingButtonTypeFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class MissingButtonTypeFixerTest extends AbstractFixerTestCase
{
    /**
     * @return \Shopsys\CodingStandards\CsFixer\MissingButtonTypeFixer
     */
    protected function createFixerService(): MissingButtonTypeFixer
    {
        return new MissingButtonTypeFixer();
    }

    /**
     * {@inheritDoc}
     */
    public function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.html.twig', __DIR__ . '/wrong/wrong.html.twig'];
        yield [__DIR__ . '/correct/correct.html.twig'];
    }
}
