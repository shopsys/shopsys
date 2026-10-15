<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TableCellsField;

class TableCellsFieldTest extends TestCase
{
    /**
     * @return iterable<string, array{value: mixed, expectedResult: mixed}>
     */
    public static function getNormalizeValueData(): iterable
    {
        yield 'valid grid is kept' => [
            'value' => [['a', 'b'], ['c', 'd']],
            'expectedResult' => [['a', 'b'], ['c', 'd']],
        ];

        yield 'scalar cells are cast to string' => [
            'value' => [[1, 2.5, true]],
            'expectedResult' => [['1', '2.5', '1']],
        ];

        yield 'non-scalar cells become empty' => [
            'value' => [['a', null, ['x']]],
            'expectedResult' => [['a', '', '']],
        ];

        yield 'non-array row becomes empty row' => [
            'value' => [['a'], 'b'],
            'expectedResult' => [['a'], []],
        ];

        yield 'keys are renumbered' => [
            'value' => ['x' => ['k' => 'a']],
            'expectedResult' => [['a']],
        ];

        yield 'non-array becomes empty grid' => [
            'value' => 'a',
            'expectedResult' => [],
        ];
    }

    #[DataProvider('getNormalizeValueData')]
    public function testNormalizeValue(mixed $value, mixed $expectedResult): void
    {
        $field = TableCellsField::create('cells');

        $this->assertSame($expectedResult, $field->normalizeValue($value));
    }

    public function testExtractTextJoinsNonEmptyCells(): void
    {
        $field = TableCellsField::create('cells');

        $this->assertSame("a\nb\nd", $field->extractText([['a', 'b'], ['', 'd']]));
    }
}
