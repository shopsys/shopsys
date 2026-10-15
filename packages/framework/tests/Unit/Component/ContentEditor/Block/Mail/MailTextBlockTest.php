<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Mail;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailTextBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web\AbstractWebBlockTestCase;

class MailTextBlockTest extends AbstractWebBlockTestCase
{
    public function testIsMailBlockWithRequiredHtml(): void
    {
        $block = new MailTextBlock();

        $this->assertSame('mail-text', $block->getType());
        $this->assertSame([ContentProfileEnum::MAIL], $block->getProfiles());
        $this->assertSame(['html' => ''], $block->getDefaultData());
        $this->assertTrue($block->getFields()[0]->isRequired());
    }

    public function testExtractTextReturnsHtml(): void
    {
        $this->assertSame('<p>Order {order_number}</p>', (new MailTextBlock())->extractText(['html' => '<p>Order {order_number}</p>']));
    }
}
