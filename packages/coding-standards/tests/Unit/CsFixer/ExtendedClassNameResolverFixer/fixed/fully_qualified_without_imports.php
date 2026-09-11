<?php

declare(strict_types=1);

namespace App\Model;

use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;

class Example
{
    public function run(): string
    {
        return ExtendedClassNameResolver::resolve(\Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\DummyFacade::class)::doSomething('a');
    }
}
