<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\WysiwygField;

class WysiwygFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{value: mixed, expectedResult: mixed}>
     */
    public static function getNormalizeValueData(): iterable
    {
        yield 'keeps html untouched, sanitizing is up to the normalizer' => [
            'value' => ' <p onclick="x">Hi</p> ',
            'expectedResult' => '<p onclick="x">Hi</p>',
        ];

        yield 'non-scalar becomes empty string' => [
            'value' => [],
            'expectedResult' => '',
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = WysiwygField::create('html');

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }

    public function testExtractTextReturnsHtml(): void
    {
        $this->assertSame('<p>Hi</p>', WysiwygField::create('html')->extractText('<p>Hi</p>'));
    }
}
