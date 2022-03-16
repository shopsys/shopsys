<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ValidVariableNameSniff;

use Shopsys\CodingStandards\Sniffs\ValidVariableNameSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ValidVariableNameSniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getSniffClassName(): string
    {
        return ValidVariableNameSniff::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getWrongFiles(): iterable
    {
        yield [__DIR__ . '/wrong/wrong.inc'];
    }
}
