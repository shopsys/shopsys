<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\MissingButtonTypeFixer;

use Override;
use Shopsys\CodingStandards\CsFixer\MissingButtonTypeFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class MissingButtonTypeFixerTest extends AbstractFixerTestCase
{
    #[Override]
    protected function createFixerService(): MissingButtonTypeFixer
    {
        return new MissingButtonTypeFixer();
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
