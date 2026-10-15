<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\MediaField;

class MediaFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{value: mixed, expectedResult: mixed}>
     */
    public static function getNormalizeValueData(): iterable
    {
        yield 'int is kept' => [
            'value' => 12,
            'expectedResult' => 12,
        ];

        yield 'numeric string becomes int' => [
            'value' => '12',
            'expectedResult' => 12,
        ];

        yield 'zero becomes null' => [
            'value' => 0,
            'expectedResult' => null,
        ];

        yield 'garbage becomes null' => [
            'value' => 'abc',
            'expectedResult' => null,
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = MediaField::create('mediaId');

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }
}
