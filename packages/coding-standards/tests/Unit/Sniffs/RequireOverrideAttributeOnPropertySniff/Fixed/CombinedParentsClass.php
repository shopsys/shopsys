<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff;

use Override;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff\Base\PropertyInterface;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff\Base\PropertyParentClass;

class CombinedParentsClass extends PropertyParentClass implements PropertyInterface
{
    #[Override]
    protected string $name = 'overridden';

    #[Override]
    public string $label = '';

    public string $uniqueProperty = '';
}
