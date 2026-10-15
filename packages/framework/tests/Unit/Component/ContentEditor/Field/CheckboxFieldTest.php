<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\CheckboxField;

class CheckboxFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{value: mixed, expectedResult: mixed}>
     */
    public static function getNormalizeValueData(): iterable
    {
        yield 'true stays true' => [
            'value' => true,
            'expectedResult' => true,
        ];

        yield '"1" becomes true' => [
            'value' => '1',
            'expectedResult' => true,
        ];

        yield '"0" becomes false' => [
            'value' => '0',
            'expectedResult' => false,
        ];

        yield 'null becomes false' => [
            'value' => null,
            'expectedResult' => false,
        ];

        yield 'garbage becomes false' => [
            'value' => 'abc',
            'expectedResult' => false,
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = CheckboxField::create('enabled');

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }
}
