<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source;

class ExcludedDummyValue
{
    public static function create(): self
    {
        return new self();
    }
}
