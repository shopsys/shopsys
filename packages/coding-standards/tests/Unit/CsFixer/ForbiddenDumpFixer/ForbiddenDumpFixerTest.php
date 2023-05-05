<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\ForbiddenDumpFixer;

use Shopsys\CodingStandards\CsFixer\ForbiddenDumpFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class ForbiddenDumpFixerTest extends AbstractFixerTestCase
{
    /**
     * @return \Shopsys\CodingStandards\CsFixer\ForbiddenDumpFixer
     */
    protected function createFixerService(): ForbiddenDumpFixer
    {
        return new ForbiddenDumpFixer();
    }

    /**
     * {@inheritdoc}
     */
    public function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.html.twig', __DIR__ . '/wrong/wrong.html.twig'];
        yield [__DIR__ . '/correct/correct.html.twig'];
    }
}
