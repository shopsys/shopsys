<?php

declare(strict_types=1);

namespace App\Model;

use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\DummyFacade;

class Example
{
    public function run(): string
    {
        return ExtendedClassNameResolver::resolve(DummyFacade::class)::doSomething('a');
    }
}
