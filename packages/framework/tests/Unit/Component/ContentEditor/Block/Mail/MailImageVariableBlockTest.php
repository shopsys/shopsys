<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Mail;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailImageVariableBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web\AbstractWebBlockTestCase;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class MailImageVariableBlockTest extends AbstractWebBlockTestCase
{
    public function testDefaults(): void
    {
        $block = new MailImageVariableBlock();

        $this->assertSame('mail-image-variable', $block->getType());
        $this->assertSame([ContentProfileEnum::MAIL], $block->getProfiles());
        $this->assertSame(['variable' => null, 'alt' => ''], $block->getDefaultData());
        $this->assertTrue(ContentEditorTestFactory::getField($block, 'variable')->isRequired());
    }

    public function testExportKeepsThePlaceholderForTheMailer(): void
    {
        $data = ['variable' => '{product_image}', 'alt' => '{product_name}'];

        $this->assertSame($data, (new MailImageVariableBlock())->export($data, 1));
    }
}
