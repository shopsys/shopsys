<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source;

interface ExcludedDummyInterface
{
    public static function create(): self;
}
