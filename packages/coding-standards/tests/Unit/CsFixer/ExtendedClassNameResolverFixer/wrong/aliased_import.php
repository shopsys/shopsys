<?php

declare(strict_types=1);

namespace App\Model;

use Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\DummyFacade as BaseDummyFacade;

class Example
{
    public function run(): string
    {
        return BaseDummyFacade::doSomething('a');
    }
}
