<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base;

abstract class AbstractChainedTestClass implements TestInterface, SecondInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'name';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    abstract protected function doSomething(): void;

    private function doSomethingElse(): void
    {
    }
}
