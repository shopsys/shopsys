<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff;

use Override;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\AbstractTestClass;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\TestInterface;

class CombinedParentsClass extends AbstractTestClass implements TestInterface
{
    #[Override]
    public function getName(): string
    {
        return 'name';
    }

    #[Override]
    public function getDescription(): string
    {
        return 'description';
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
