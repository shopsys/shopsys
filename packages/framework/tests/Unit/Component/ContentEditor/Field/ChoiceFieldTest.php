<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ChoiceField;

class ChoiceFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{value: mixed, expectedResult: mixed}>
     */
    public static function getNormalizeValueData(): iterable
    {
        yield 'allowed choice is kept' => [
            'value' => 'left',
            'expectedResult' => 'left',
        ];

        yield 'unknown choice falls back to default' => [
            'value' => 'right',
            'expectedResult' => 'center',
        ];

        yield 'null falls back to default' => [
            'value' => null,
            'expectedResult' => 'center',
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = ChoiceField::create('position', ['left', 'center'])->default('center');

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }
}
