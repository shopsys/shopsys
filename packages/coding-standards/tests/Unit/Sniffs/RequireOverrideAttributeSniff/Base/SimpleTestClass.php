<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base;

class SimpleTestClass
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'name';
    }

    public function process(): void
    {
        // Base implementation
    }

    public function calculate(): int
    {
        return 42;
    }
}
