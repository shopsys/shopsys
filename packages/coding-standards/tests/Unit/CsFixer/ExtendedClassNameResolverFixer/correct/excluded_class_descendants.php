<?php

declare(strict_types=1);

namespace App\Model;

use Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\ExcludedDummyImplementation;
use Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\ExcludedDummyValue;
use Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\ExcludedDummyValueChild;

class Example
{
    public function run(): void
    {
        ExcludedDummyValue::create();
        ExcludedDummyValueChild::create();
        ExcludedDummyImplementation::create();
    }
}
