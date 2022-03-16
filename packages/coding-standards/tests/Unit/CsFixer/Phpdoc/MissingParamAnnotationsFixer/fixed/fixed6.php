<?php

namespace Shopsys\CodingStandards\Tests;

use Tests\CodingStandards\Unit\CsFixer\Phpdoc\FunctionAnnotationFixer\Source\Naming;

final class SomeClassWithoutAnnotation
{
    /**
     * @var \Tests\CodingStandards\Unit\CsFixer\Phpdoc\FunctionAnnotationFixer\Source\Naming
     */
    private $naming;

    /**
     * @param \Tests\CodingStandards\Unit\CsFixer\Phpdoc\FunctionAnnotationFixer\Source\Naming $naming
     */
    public function __construct(Naming $naming)
    {
    }
}
