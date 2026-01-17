<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff;

use Override;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\ExtendedInterface;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\SecondInterface;

class ChainedInterfacesClass implements ExtendedInterface, SecondInterface
{
    #[Override]
    public function getName(): string
    {
        return '';
    }

    #[Override]
    public function process(): void
    {
    }

    #[Override]
    public function extendedMethod(): void
    {
    }
}
