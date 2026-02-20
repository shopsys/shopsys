<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff\Base;

interface PropertyInterface
{
    public string $label { get; }
}
