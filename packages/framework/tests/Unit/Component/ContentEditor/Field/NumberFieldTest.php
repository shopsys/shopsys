<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\NumberField;

class NumberFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{value: mixed, expectedResult: mixed}>
     */
    public static function getNormalizeValueData(): iterable
    {
        yield 'int is kept' => [
            'value' => 5,
            'expectedResult' => 5,
        ];

        yield 'float is kept' => [
            'value' => 5.5,
            'expectedResult' => 5.5,
        ];

        yield 'integer string becomes int' => [
            'value' => '5',
            'expectedResult' => 5,
        ];

        yield 'decimal string becomes float' => [
            'value' => '5.5',
            'expectedResult' => 5.5,
        ];

        yield 'invalid value falls back to default' => [
            'value' => 'abc',
            'expectedResult' => 3,
        ];

        yield 'null falls back to default' => [
            'value' => null,
            'expectedResult' => 3,
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = NumberField::create('rows')->default(3);

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }
}
