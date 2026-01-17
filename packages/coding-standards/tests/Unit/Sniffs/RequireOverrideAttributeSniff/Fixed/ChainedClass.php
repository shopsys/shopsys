<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff;

use Override;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\AbstractChainedTestClass;

class ChainedClass extends AbstractChainedTestClass
{
    #[Override]
    public function getName(): string
    {
        return 'name';
    }

    #[Override]
    public function getDescription(): string
    {
        return '';
    }

    #[Override]
    public function process(): void
    {
    }

    #[Override]
    protected function doSomething(): void
    {
    }

    public function uniqueMethod(): string
    {
        return 'This method should not need Override';
    }
}
