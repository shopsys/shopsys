<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base;

interface TestInterface
{
    public function getName(): string;
}
