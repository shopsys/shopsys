<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextareaField;

class TextareaFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{value: mixed, expectedResult: mixed}>
     */
    public static function getNormalizeValueData(): iterable
    {
        yield 'trims string' => [
            'value' => "  a\nb ",
            'expectedResult' => "a\nb",
        ];

        yield 'null becomes empty string' => [
            'value' => null,
            'expectedResult' => '',
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = TextareaField::create('note');

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }
}
