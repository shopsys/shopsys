<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff;

use Override;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\SimpleTestClass;

class SimpleWithParentClass extends SimpleTestClass
{
    #[Override]
    public function getName(): string
    {
        return 'name';
    }

    #[Override]
    public function process(): void
    {
    }

    public function uniqueMethod(): string
    {
        return '';
    }
}
