<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source;

use Override;

class ExcludedDummyImplementation implements ExcludedDummyInterface
{
    #[Override]
    public static function create(): self
    {
        return new self();
    }
}
