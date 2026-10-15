<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ProductsField;

class ProductsFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{value: mixed, expectedResult: mixed}>
     */
    public static function getNormalizeValueData(): iterable
    {
        yield 'ids are cast and deduplicated' => [
            'value' => ['1', 2, 2, 3.0],
            'expectedResult' => [1, 2, 3],
        ];

        yield 'invalid ids are dropped' => [
            'value' => ['x', 0, -4, null, 5],
            'expectedResult' => [5],
        ];

        yield 'non-array becomes empty list' => [
            'value' => '1',
            'expectedResult' => [],
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = ProductsField::create('productIds');

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }
}
