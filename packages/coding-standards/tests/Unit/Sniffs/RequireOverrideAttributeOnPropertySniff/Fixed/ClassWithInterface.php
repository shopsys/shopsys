<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff;

use Override;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff\Base\PropertyInterface;

class ClassWithInterface implements PropertyInterface
{
    #[Override]
    public string $label = '';

    public string $uniqueProperty = '';
}
