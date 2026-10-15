<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\TextBlock;

class TextBlockTest extends AbstractWebBlockTestCase
{
    public function testHtmlIsRequiredAndDefaultsToEmpty(): void
    {
        $block = new TextBlock();

        $this->assertSame(['html' => ''], $block->getDefaultData());
        $this->assertTrue($block->getFields()[0]->isRequired());
    }

    public function testExtractTextReturnsHtml(): void
    {
        $this->assertSame('<p>Hello</p>', (new TextBlock())->extractText(['html' => '<p>Hello</p>']));
    }
}
