<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Helper;

use Iterator;
use PHPUnit\Framework\TestCase;
use Shopsys\CodingStandards\Helper\ShopsysFixerNaming;
use Tests\CodingStandards\Unit\CsFixer\Phpdoc\MissingParamAnnotationsFixer\MissingParamAnnotationsFixer;

final class ShopsysFixerNamingTest extends TestCase
{
    /**
     * @dataProvider provideCases()
     * @param string $class
     * @param string $expectedName
     */
    public function test(string $class, string $expectedName): void
    {
        $this->assertSame($expectedName, ShopsysFixerNaming::createFromClass($class));
    }

    /**
     * @return \Iterator
     */
    public function provideCases(): Iterator
    {
        yield ['CodingStandard\\SomeFixer', 'Shopsys/some'];
        yield [MissingParamAnnotationsFixer::class, 'Shopsys/missing_param_annotations'];
    }
}
