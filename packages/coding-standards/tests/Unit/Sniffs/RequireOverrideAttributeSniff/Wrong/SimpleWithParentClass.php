<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff;

use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\SimpleTestClass;

class SimpleWithParentClass extends SimpleTestClass
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
}
