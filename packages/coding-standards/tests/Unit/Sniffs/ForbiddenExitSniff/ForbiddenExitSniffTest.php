<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ForbiddenExitSniff;

use Shopsys\CodingStandards\Sniffs\ForbiddenExitSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ForbiddenExitSniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getSniffClassName(): string
    {
        return ForbiddenExitSniff::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getWrongFiles(): iterable
    {
        yield [__DIR__ . '/wrong/wrong.php.inc'];
    }
}
