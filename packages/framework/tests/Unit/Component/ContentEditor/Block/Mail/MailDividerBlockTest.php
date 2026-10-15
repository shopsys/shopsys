<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Mail;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailDividerBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web\AbstractWebBlockTestCase;

class MailDividerBlockTest extends AbstractWebBlockTestCase
{
    public function testHasNoFields(): void
    {
        $block = new MailDividerBlock();

        $this->assertSame('mail-divider', $block->getType());
        $this->assertSame([ContentProfileEnum::MAIL], $block->getProfiles());
        $this->assertSame([], $block->getFields());
        $this->assertSame('tabler:minus', $block->getIcon());
    }
}
