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
        yield 'allowed placeholder is kept' => [
            'value' => '{product_image}',
            'expectedResult' => '{product_image}',
        ];

        yield 'unknown placeholder becomes null' => [
            'value' => '{other}',
            'expectedResult' => null,
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = VariableField::create('src', ['{product_image}']);

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }
}
