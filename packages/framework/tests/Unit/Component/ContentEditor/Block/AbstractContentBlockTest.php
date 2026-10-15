<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block;

use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\DummyColumnsBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\DummyImageBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\DummyTextBlock;

class AbstractContentBlockTest extends TestCase
{
    public function testTypeAndProfilesAreReadFromAttribute(): void
    {
        $block = new DummyColumnsBlock();

        $this->assertSame('columns', $block->getType());
        $this->assertSame([ContentProfileEnum::WEB, ContentProfileEnum::MAIL], $block->getProfiles());
    }

    public function testDefaults(): void
    {
        $block = new DummyImageBlock();

        $this->assertNull($block->getIcon());
        $this->assertSame(AbstractContentBlock::CATEGORY_CONTENT, $block->getCategory());
        $this->assertSame(['mediaId' => null, 'alt' => '', 'position' => 'center'], $block->getDefaultData());
        $this->assertSame(['alt' => 'x'], $block->export(['alt' => 'x'], 1));
    }

    public function testExtractTextJoinsTextFieldValues(): void
    {
        $block = new DummyImageBlock();

        $this->assertSame('A cat', $block->extractText(['mediaId' => 5, 'alt' => 'A cat', 'position' => 'left']));
        $this->assertSame('', (new DummyTextBlock())->extractText([]));
    }

    public function testMissingAttributeThrows(): void
    {
        $block = new class() extends AbstractContentBlock {
            #[Override]
            public function getLabel(): string
            {
                return '';
            }

            #[Override]
            public function getFields(): array
            {
                return [];
            }
        };

        $this->expectException(InvalidArgumentException::class);

        $block->getType();
    }
}
