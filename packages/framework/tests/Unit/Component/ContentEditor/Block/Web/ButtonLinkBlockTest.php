<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\ButtonLinkBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class ButtonLinkBlockTest extends AbstractWebBlockTestCase
{
    public function testDefaults(): void
    {
        $this->assertSame(
            [
                'text' => '',
                'url' => null,
                'openInNewTab' => false,
                'position' => 'center',
                'color' => '#00C8B7',
            ],
            (new ButtonLinkBlock())->getDefaultData(),
        );
    }

    public function testTextAndUrlAreRequired(): void
    {
        $block = new ButtonLinkBlock();

        $this->assertTrue(ContentEditorTestFactory::getField($block, 'text')->isRequired());
        $this->assertTrue(ContentEditorTestFactory::getField($block, 'url')->isRequired());
    }

    public function testExtractTextReturnsButtonText(): void
    {
        $this->assertSame('Buy now', (new ButtonLinkBlock())->extractText([
            'text' => 'Buy now',
            'url' => '/shop',
        ]));
    }
}
