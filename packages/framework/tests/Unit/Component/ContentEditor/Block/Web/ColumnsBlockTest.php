<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\ColumnsBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class ColumnsBlockTest extends AbstractWebBlockTestCase
{
    /**
     * @return iterable<string, array{layout: string, expectedSlotCount: int}>
     */
    public static function getSlotCountData(): iterable
    {
        yield 'halves' => [
            'layout' => '1/2-1/2',
            'expectedSlotCount' => 2,
        ];

        yield 'third and two thirds' => [
            'layout' => '1/3-2/3',
            'expectedSlotCount' => 2,
        ];

        yield 'two thirds and third' => [
            'layout' => '2/3-1/3',
            'expectedSlotCount' => 2,
        ];

        yield 'thirds' => [
            'layout' => '1/3-1/3-1/3',
            'expectedSlotCount' => 3,
        ];
    }

    #[DataProvider('getSlotCountData')]
    public function testGetSlotCount(string $layout, int $expectedSlotCount): void
    {
        $this->assertSame($expectedSlotCount, (new ColumnsBlock())->getSlotCount(['layout' => $layout]));
    }

    public function testDefaultsAndCategory(): void
    {
        $block = new ColumnsBlock();

        $this->assertSame(['layout' => '1/2-1/2'], $block->getDefaultData());
        $this->assertSame(AbstractContentBlock::CATEGORY_LAYOUT, $block->getCategory());
        $this->assertSame('1/2-1/2', ContentEditorTestFactory::getField($block, 'layout')->normalizeValue('4/4'));
    }
}
