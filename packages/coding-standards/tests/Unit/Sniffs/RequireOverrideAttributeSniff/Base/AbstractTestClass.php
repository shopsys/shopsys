<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base;

abstract class AbstractTestClass
{
    abstract public function getDescription(): string;

    abstract protected function doSomething(): void;

    public function process(): void
    {
    }
}
