<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff;

use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\AbstractChainedTestClass;

class ChainedClass extends AbstractChainedTestClass
{
    public function getName(): string
    {
        return 'name';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function process(): void
    {
    }

    protected function doSomething(): void
    {
    }

    public function uniqueMethod(): string
    {
        return 'This method should not need Override';
    }
}
