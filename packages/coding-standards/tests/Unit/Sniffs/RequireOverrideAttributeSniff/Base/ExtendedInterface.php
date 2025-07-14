<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base;

interface ExtendedInterface extends TestInterface
{
    public function extendedMethod(): void;
}
