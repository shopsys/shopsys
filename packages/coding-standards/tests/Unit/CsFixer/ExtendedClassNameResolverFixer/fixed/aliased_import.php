<?php

declare(strict_types=1);

namespace App\Model;

use Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\DummyFacade as BaseDummyFacade;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;

class Example
{
    public function run(): string
    {
        return ExtendedClassNameResolver::resolve(BaseDummyFacade::class)::doSomething('a');
    }
}
