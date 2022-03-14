<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ObjectIsCreatedByFactorySniff;

use Shopsys\CodingStandards\Sniffs\ObjectIsCreatedByFactorySniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ObjectIsCreatedByFactorySniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getSniffClassName(): string
    {
        return ObjectIsCreatedByFactorySniff::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getCorrectFiles(): iterable
    {
        yield [__DIR__ . '/Correct/PostFactory.php'];
    }

    /**
     * {@inheritDoc}
     */
    public function getWrongFiles(): iterable
    {
        require_once __DIR__ . '/Wrong/PostFactory.php';

        yield [__DIR__ . '/Wrong/SomeController.php'];
    }
}
