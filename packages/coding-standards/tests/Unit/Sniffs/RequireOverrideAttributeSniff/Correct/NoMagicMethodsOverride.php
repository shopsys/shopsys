<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff;

use Override;
use Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff\Base\SimpleTestClass;

class NoMagicMethodsOverride extends SimpleTestClass
{
    public function __construct()
    {
        parent::__construct();
    }

    // Magic methods should NOT have #[Override] attribute
    public function __destruct()
    {
        // Destructor
    }
    
    public function __toString(): string
    {
        return 'string representation';
    }
    
    public function __clone(): void
    {
        // Clone
    }
    
    /**
     * @return string
     */
    #[Override]
    public function getName(): string
    {
        return 'name with magic methods';
    }
}