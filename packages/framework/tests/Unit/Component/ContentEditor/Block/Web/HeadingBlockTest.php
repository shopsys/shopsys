<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\HeadingBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class HeadingBlockTest extends AbstractWebBlockTestCase
{
    public function testDefaults(): void
    {
        $this->assertSame([
            'text' => '',
            'level' => 'h2',
        ], (new HeadingBlock())->getDefaultData());
    }

    public function testTextIsRequired(): void
    {
        $this->assertTrue(ContentEditorTestFactory::getField(new HeadingBlock(), 'text')->isRequired());
    }

    public function testUnknownLevelFallsBackToDefault(): void
    {
        $level = ContentEditorTestFactory::getField(new HeadingBlock(), 'level');

        $this->assertSame('h3', $level->normalizeValue('h3'));
        $this->assertSame('h2', $level->normalizeValue('h1'));
    }

    public function testExtractTextReturnsHeadingText(): void
    {
        $this->assertSame('Title', (new HeadingBlock())->extractText([
            'text' => 'Title',
            'level' => 'h3',
        ]));
    }
}
