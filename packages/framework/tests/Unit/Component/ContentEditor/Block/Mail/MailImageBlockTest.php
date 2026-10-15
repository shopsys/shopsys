<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Mail;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ExportContext;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail\MailImageBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Media\Media;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web\AbstractWebBlockTestCase;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class MailImageBlockTest extends AbstractWebBlockTestCase
{
    public function testDefaults(): void
    {
        $block = $this->createBlock($this->createStub(MediaFacade::class));

        $this->assertSame('mail-image', $block->getType());
        $this->assertSame([ContentProfileEnum::MAIL], $block->getProfiles());
        $this->assertSame(['mediaId' => null, 'alt' => '', 'position' => 'center', 'link' => null], $block->getDefaultData());
    }

    public function testLinkAcceptsVariables(): void
    {
        $link = ContentEditorTestFactory::getField($this->createBlock($this->createStub(MediaFacade::class)), 'link');

        $this->assertSame('{product_url}', $link->normalizeValue('{product_url}'));
    }

    public function testExportUsesImageLimitedTo600AndCapsTheWidth(): void
    {
        $media = $this->createStub(Media::class);
        $media->method('getWidth')->willReturn(1200);
        $media->method('getHeight')->willReturn(800);
        $mediaFacade = $this->createMock(MediaFacade::class);
        $mediaFacade->method('getById')->willReturn($media);
        $mediaFacade->expects($this->once())->method('getImageUrl')->with($this->anything(), $media, 600)->willReturn('https://example.com/image.jpg?width=600');

        $result = $this->createBlock($mediaFacade)->export(['mediaId' => 12], new ExportContext(1, 'en'));

        $this->assertSame(['mediaId' => 12, 'url' => 'https://example.com/image.jpg?width=600', 'width' => 600], $result);
    }

    public function testExportKeepsSmallerWidth(): void
    {
        $media = $this->createStub(Media::class);
        $media->method('getWidth')->willReturn(300);
        $mediaFacade = $this->createStub(MediaFacade::class);
        $mediaFacade->method('getById')->willReturn($media);
        $mediaFacade->method('getImageUrl')->willReturn('https://example.com/image.jpg');

        $result = $this->createBlock($mediaFacade)->export(['mediaId' => 12], new ExportContext(1, 'en'));

        $this->assertSame(300, $result['width']);
    }

    private function createBlock(MediaFacade $mediaFacade): MailImageBlock
    {
        $domain = $this->createStub(Domain::class);
        $domain->method('getDomainConfigById')->willReturn($this->createStub(DomainConfig::class));

        return new MailImageBlock($mediaFacade, $domain);
    }
}
