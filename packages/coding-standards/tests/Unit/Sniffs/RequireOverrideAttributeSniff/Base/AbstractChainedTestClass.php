<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base;

use Override;

abstract class AbstractChainedTestClass implements TestInterface, SecondInterface
{
    #[Override]
    public function getName(): string
    {
        return 'name';
    }

    public function getDescription(): string
    {
        return '';
    }

    abstract protected function doSomething(): void;

    private function doSomethingElse(): void
    {
    }
}
