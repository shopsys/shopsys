<?php

namespace Shopsys\CodingStandards\Tests;

use stdClass;
use Tests\CodingStandards\Unit\CsFixer\Phpdoc\FunctionAnnotationFixer\Source\NamespacedType;

final class SomeClass
{
    public function function1(?stdClass $value): void
    {
    }

    public function function2(?NamespacedType $value): void
    {
    }

    public function function3(int $value = null): void
    {
    }
}
