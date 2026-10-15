<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ExportContext;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\TableBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class TableBlockTest extends AbstractWebBlockTestCase
{
    public function testDefaultsAreValid(): void
    {
        $block = new TableBlock();
        $defaults = $block->getDefaultData();

        $this->assertSame(2, $defaults['rows']);
        $this->assertSame(2, $defaults['columns']);
        $this->assertSame([['', ''], ['', '']], $defaults['cells']);
        $this->assertSame([], ContentEditorTestFactory::validateField($block, 'rows', $defaults['rows']));
        $this->assertSame([], ContentEditorTestFactory::validateField($block, 'columns', $defaults['columns']));
    }

    /**
     * @return iterable<string, array{field: string, value: int, isValid: bool}>
     */
    public static function getSizeConstraintData(): iterable
    {
        yield 'rows min' => ['field' => 'rows', 'value' => 1, 'isValid' => true];

        yield 'rows max' => ['field' => 'rows', 'value' => 20, 'isValid' => true];

        yield 'rows below min' => ['field' => 'rows', 'value' => 0, 'isValid' => false];

        yield 'rows above max' => ['field' => 'rows', 'value' => 21, 'isValid' => false];

        yield 'columns min' => ['field' => 'columns', 'value' => 1, 'isValid' => true];

        yield 'columns max' => ['field' => 'columns', 'value' => 8, 'isValid' => true];

        yield 'columns below min' => ['field' => 'columns', 'value' => 0, 'isValid' => false];

        yield 'columns above max' => ['field' => 'columns', 'value' => 9, 'isValid' => false];
    }

    #[DataProvider('getSizeConstraintData')]
    public function testSizeConstraints(string $field, int $value, bool $isValid): void
    {
        $violations = ContentEditorTestFactory::validateField(new TableBlock(), $field, $value);

        $this->assertSame($isValid, $violations === []);
    }

    /**
     * @return iterable<string, array{data: array<string, mixed>, expectedCells: string[][]}>
     */
    public static function getExportData(): iterable
    {
        yield 'matching grid is kept' => [
            'data' => ['rows' => 2, 'columns' => 2, 'cells' => [['a', 'b'], ['c', 'd']]],
            'expectedCells' => [['a', 'b'], ['c', 'd']],
        ];

        yield 'missing rows and cells are padded' => [
            'data' => ['rows' => 3, 'columns' => 2, 'cells' => [['a']]],
            'expectedCells' => [['a', ''], ['', ''], ['', '']],
        ];

        yield 'extra rows and cells are trimmed' => [
            'data' => ['rows' => 1, 'columns' => 1, 'cells' => [['a', 'b'], ['c', 'd']]],
            'expectedCells' => [['a']],
        ];

        yield 'size out of range is clamped' => [
            'data' => ['rows' => 0, 'columns' => 100, 'cells' => []],
            'expectedCells' => [array_fill(0, 8, '')],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param string[][] $expectedCells
     */
    #[DataProvider('getExportData')]
    public function testExportFitsCellsToRowsAndColumns(array $data, array $expectedCells): void
    {
        $result = (new TableBlock())->export($data, new ExportContext(1, 'en'));

        $this->assertSame($expectedCells, $result['cells']);
        $this->assertSame(count($expectedCells), $result['rows']);
        $this->assertSame(count($expectedCells[0]), $result['columns']);
    }

    public function testExtractTextReturnsCellTexts(): void
    {
        $text = (new TableBlock())->extractText(['rows' => 1, 'columns' => 2, 'cells' => [['a', 'b']]]);

        $this->assertSame("a\nb", $text);
    }
}
