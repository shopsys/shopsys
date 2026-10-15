<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Mail;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ExportContext;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailColumnsBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web\AbstractWebBlockTestCase;

class MailColumnsBlockTest extends AbstractWebBlockTestCase
{
    public function testHasTwoSlotsOfHalfWidthAndNoFields(): void
    {
        $block = new MailColumnsBlock();

        $this->assertSame('mail-columns', $block->getType());
        $this->assertSame([ContentProfileEnum::MAIL], $block->getProfiles());
        $this->assertSame(2, $block->getSlotCount([]));
        $this->assertSame(['layout' => '1/2-1/2'], $block->export([], new ExportContext(1, 'en')));
        $this->assertSame([], $block->getFields());
        $this->assertSame(AbstractContentBlock::CATEGORY_LAYOUT, $block->getCategory());
        $this->assertSame('tabler:columns-2', $block->getIcon());
    }
}
