<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Mail;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailButtonBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web\AbstractWebBlockTestCase;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class MailButtonBlockTest extends AbstractWebBlockTestCase
{
    public function testDefaults(): void
    {
        $block = new MailButtonBlock();

        $this->assertSame('mail-button', $block->getType());
        $this->assertSame([ContentProfileEnum::MAIL], $block->getProfiles());
        $this->assertSame(
            [
                'text' => '',
                'url' => null,
                'position' => 'center',
                'color' => '#00C8B7',
            ],
            $block->getDefaultData(),
        );
    }

    public function testTextAndUrlAreRequired(): void
    {
        $block = new MailButtonBlock();

        $this->assertTrue(ContentEditorTestFactory::getField($block, 'text')->isRequired());
        $this->assertTrue(ContentEditorTestFactory::getField($block, 'url')->isRequired());
    }

    public function testUrlAcceptsVariables(): void
    {
        $url = ContentEditorTestFactory::getField(new MailButtonBlock(), 'url');

        $this->assertSame('{order_url}', $url->normalizeValue('{order_url}'));
    }
}
