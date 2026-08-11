<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\MissingLinkRelNoopenerFixer;

use Override;
use Shopsys\CodingStandards\CsFixer\MissingLinkRelNoopenerFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class MissingLinkRelNoopenerFixerTest extends AbstractFixerTestCase
{
    #[Override]
    protected function createFixerService(): MissingLinkRelNoopenerFixer
    {
        return new MissingLinkRelNoopenerFixer();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/fixed.html.twig', __DIR__ . '/wrong/wrong.html.twig'];

        yield [__DIR__ . '/correct/correct.html.twig'];
    }
}
