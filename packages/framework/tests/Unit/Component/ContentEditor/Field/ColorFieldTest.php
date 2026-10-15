<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ColorField;

class ColorFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{value: mixed, expectedResult: mixed}>
     */
    public static function getNormalizeValueData(): iterable
    {
        yield 'six digit hex is kept' => [
            'value' => '#FFaa00',
            'expectedResult' => '#FFaa00',
        ];

        yield 'three digit hex is kept' => [
            'value' => '#fa0',
            'expectedResult' => '#fa0',
        ];

        yield 'css name falls back to default' => [
            'value' => 'red',
            'expectedResult' => '#000000',
        ];

        yield 'injection falls back to default' => [
            'value' => '#fff;x',
            'expectedResult' => '#000000',
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = ColorField::create('color')->default('#000000');

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }
}
