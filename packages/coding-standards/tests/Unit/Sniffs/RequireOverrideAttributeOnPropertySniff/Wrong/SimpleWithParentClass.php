<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff;

use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff\Base\PropertyParentClass;

class SimpleWithParentClass extends PropertyParentClass
{
    protected string $name = 'overridden';

    protected int $value = 42;

    private string $uniqueProperty = '';
}
