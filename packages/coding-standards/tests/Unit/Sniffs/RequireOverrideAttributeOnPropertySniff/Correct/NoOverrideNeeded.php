<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff;

use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff\Base\PropertyParentClass;

class NoOverrideNeeded extends PropertyParentClass
{
    protected string $uniqueProperty = '';

    private string $anotherUniqueProperty = '';
}
