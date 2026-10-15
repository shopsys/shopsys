<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;

class TextFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{value: mixed, expectedResult: mixed}>
     */
    public static function getNormalizeValueData(): iterable
    {
        yield 'trims string' => [
            'value' => '  Hi ',
            'expectedResult' => 'Hi',
        ];

        yield 'casts number' => [
            'value' => 12,
            'expectedResult' => '12',
        ];

        yield 'array becomes empty string' => [
            'value' => ['x'],
            'expectedResult' => '',
        ];

        yield 'null becomes empty string' => [
            'value' => null,
            'expectedResult' => '',
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = TextField::create('title');

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }

    public function testExtractTextReturnsValue(): void
    {
        $this->assertSame('Hi', TextField::create('title')->extractText(' Hi '));
    }
}
