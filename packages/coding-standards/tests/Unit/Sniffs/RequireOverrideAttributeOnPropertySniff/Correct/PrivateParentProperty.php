<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff;

use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff\Base\PropertyParentClass;

class PrivateParentProperty extends PropertyParentClass
{
    private string $secret = 'my secret';
}
