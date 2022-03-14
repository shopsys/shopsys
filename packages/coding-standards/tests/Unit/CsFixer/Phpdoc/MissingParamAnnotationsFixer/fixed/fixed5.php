<?php

namespace Shopsys\CodingStandards\Tests;

use stdClass;
use Tests\CodingStandards\Unit\CsFixer\Phpdoc\FunctionAnnotationFixer\Source\NamespacedType;

final class SomeClass
{
    /**
     * @param \stdClass|null $value
     */
    public function function1(?stdClass $value)
    {
    }

    /**
     * @param \Tests\CodingStandards\Unit\CsFixer\Phpdoc\FunctionAnnotationFixer\Source\NamespacedType|null $value
     */
    public function function2(?NamespacedType $value)
    {
    }

    /**
     * @param int|null $value
     */
    public function function3(int $value = null)
    {
    }
}
