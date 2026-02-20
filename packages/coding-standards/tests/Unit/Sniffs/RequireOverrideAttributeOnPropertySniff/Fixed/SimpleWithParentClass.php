<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff;

use Override;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff\Base\PropertyParentClass;

class SimpleWithParentClass extends PropertyParentClass
{
    #[Override]
    protected string $name = 'overridden';

    #[Override]
    protected int $value = 42;

    private string $uniqueProperty = '';
}
