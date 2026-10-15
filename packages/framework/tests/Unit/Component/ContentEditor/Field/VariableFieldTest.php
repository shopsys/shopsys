<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\VariableField;

class VariableFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{value: mixed, expectedResult: mixed}>
     */
    public static function getNormalizeValueData(): iterable
    {
        yield 'placeholder is kept' => [
            'value' => '{product_image}',
            'expectedResult' => '{product_image}',
        ];

        yield 'placeholder with a digit is kept' => [
            'value' => '{image_2}',
            'expectedResult' => '{image_2}',
        ];

        yield 'placeholder in camel case is kept' => [
            'value' => '{productImageUrl}',
            'expectedResult' => '{productImageUrl}',
        ];

        yield 'text without braces becomes null' => [
            'value' => 'product_image',
            'expectedResult' => null,
        ];

        yield 'placeholder with a text around becomes null' => [
            'value' => 'x{product_image}',
            'expectedResult' => null,
        ];

        yield 'not a string becomes null' => [
            'value' => 5,
            'expectedResult' => null,
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = VariableField::create('src');

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }
}
