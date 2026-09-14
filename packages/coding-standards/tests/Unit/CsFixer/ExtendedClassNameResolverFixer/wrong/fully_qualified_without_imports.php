<?php

declare(strict_types=1);

namespace App\Model;

class Example
{
    public function run(): string
    {
        return \Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\DummyFacade::doSomething('a');
    }
}
