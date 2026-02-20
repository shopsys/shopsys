<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff\Base;

class PropertyParentClass
{
    protected string $name = '';

    protected int $value = 0;

    private string $secret = '';
}
