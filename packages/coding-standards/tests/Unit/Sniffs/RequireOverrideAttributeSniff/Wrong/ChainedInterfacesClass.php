<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff;

use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\ExtendedInterface;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\SecondInterface;

class ChainedInterfacesClass implements ExtendedInterface, SecondInterface
{
    public function getName(): string
    {
        return '';
    }

    public function process(): void
    {
    }

    public function extendedMethod(): void
    {
    }
}
