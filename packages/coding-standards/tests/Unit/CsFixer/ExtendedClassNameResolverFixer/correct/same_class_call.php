<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source;

class DummyFacade
{
    public static function doSomething(string $value): string
    {
        return $value;
    }

    public function run(): string
    {
        return DummyFacade::doSomething('a');
    }
}
