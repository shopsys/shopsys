<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source;

class DummyFacade
{
    public const string CONSTANT = 'constant';

    public static string $property = 'property';

    public static function doSomething(string $value): string
    {
        return $value;
    }
}
