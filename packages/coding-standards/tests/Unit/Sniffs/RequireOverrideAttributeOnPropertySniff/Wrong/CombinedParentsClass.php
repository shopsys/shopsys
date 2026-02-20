<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff;

use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff\Base\PropertyInterface;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff\Base\PropertyParentClass;

class CombinedParentsClass extends PropertyParentClass implements PropertyInterface
{
    protected string $name = 'overridden';

    public string $label = '';

    public string $uniqueProperty = '';
}
