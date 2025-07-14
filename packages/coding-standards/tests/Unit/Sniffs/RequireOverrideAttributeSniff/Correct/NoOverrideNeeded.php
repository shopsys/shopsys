<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff;

use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\SimpleTestClass;

class NoOverrideNeeded extends SimpleTestClass
{
    public function uniqueMethod(): string
    {
        return '';
    }
    
    private function privateHelperMethod(): bool
    {
        return true;
    }
}