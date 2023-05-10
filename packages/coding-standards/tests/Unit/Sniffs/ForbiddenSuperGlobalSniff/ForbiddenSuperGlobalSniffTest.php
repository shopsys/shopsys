<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ForbiddenSuperGlobalSniff;

use Shopsys\CodingStandards\Sniffs\ForbiddenSuperGlobalSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ForbiddenSuperGlobalSniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getSniffClassName(): string
    {
        return ForbiddenSuperGlobalSniff::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getWrongFiles(): iterable
    {
        yield [__DIR__ . '/wrong/env.php.inc'];

        yield [__DIR__ . '/wrong/post.php.inc'];
    }
}
