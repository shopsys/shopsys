<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff;

use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\SecondInterface;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\TestInterface;

class ClassWithInterface implements TestInterface, SecondInterface
{
    public function getName(): string
    {
        return 'name';
    }

    public function process(): void
    {
    }

    public function uniqueMethod(): string
    {
        return '';
    }

    private function doSomething(): void
    {
    }
}
