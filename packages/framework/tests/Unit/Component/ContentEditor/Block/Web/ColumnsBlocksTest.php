<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractColumnsBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ExportContext;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\ColumnsHalvesBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\ColumnsThirdsBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\ColumnsThirdTwoThirdsBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\ColumnsTwoThirdsThirdBlock;

class ColumnsBlocksTest extends AbstractWebBlockTestCase
{
    /**
     * @return iterable<string, array{block: \Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractColumnsBlock, expectedType: string, expectedLayout: string, expectedSlotCount: int, expectedIcon: string}>
     */
    public static function getColumnsBlocksData(): iterable
    {
        yield 'halves' => [
            'block' => new ColumnsHalvesBlock(),
            'expectedType' => 'columns-1-1',
            'expectedLayout' => '1/2-1/2',
            'expectedSlotCount' => 2,
            'expectedIcon' => 'tabler:columns-2',
        ];

        yield 'third and two thirds' => [
            'block' => new ColumnsThirdTwoThirdsBlock(),
            'expectedType' => 'columns-1-2',
            'expectedLayout' => '1/3-2/3',
            'expectedSlotCount' => 2,
            'expectedIcon' => 'tabler:layout-sidebar',
        ];

        yield 'two thirds and third' => [
            'block' => new ColumnsTwoThirdsThirdBlock(),
            'expectedType' => 'columns-2-1',
            'expectedLayout' => '2/3-1/3',
            'expectedSlotCount' => 2,
            'expectedIcon' => 'tabler:layout-sidebar-right',
        ];

        yield 'thirds' => [
            'block' => new ColumnsThirdsBlock(),
            'expectedType' => 'columns-1-1-1',
            'expectedLayout' => '1/3-1/3-1/3',
            'expectedSlotCount' => 3,
            'expectedIcon' => 'tabler:columns-3',
        ];
    }

    #[DataProvider('getColumnsBlocksData')]
    public function testSlotCountMatchesTheLayout(
        AbstractColumnsBlock $block,
        string $expectedType,
        string $expectedLayout,
        int $expectedSlotCount,
        string $expectedIcon,
    ): void {
        $this->assertSame($expectedType, $block->getType());
        $this->assertSame($expectedSlotCount, $block->getSlotCount([]));
    }

    #[DataProvider('getColumnsBlocksData')]
    public function testExportAddsTheLayout(
        AbstractColumnsBlock $block,
        string $expectedType,
        string $expectedLayout,
        int $expectedSlotCount,
        string $expectedIcon,
    ): void {
        $this->assertSame(['layout' => $expectedLayout], $block->export([], new ExportContext(1, 'en')));
    }

    #[DataProvider('getColumnsBlocksData')]
    public function testHasNoFieldsAndIsInLayoutCategory(
        AbstractColumnsBlock $block,
        string $expectedType,
        string $expectedLayout,
        int $expectedSlotCount,
        string $expectedIcon,
    ): void {
        $this->assertSame([], $block->getFields());
        $this->assertSame([], $block->getDefaultData());
        $this->assertSame(AbstractContentBlock::CATEGORY_LAYOUT, $block->getCategory());
    }

    #[DataProvider('getColumnsBlocksData')]
    public function testIconShowsTheLayout(
        AbstractColumnsBlock $block,
        string $expectedType,
        string $expectedLayout,
        int $expectedSlotCount,
        string $expectedIcon,
    ): void {
        $this->assertSame($expectedIcon, $block->getIcon());
    }
}
