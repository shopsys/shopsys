<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ExportContext;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\ImageBlock;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Media\Exception\MediaNotFoundException;
use Shopsys\FrameworkBundle\Model\Media\Media;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class ImageBlockTest extends AbstractWebBlockTestCase
{
    private const int MEDIA_ID = 12;

    public function testDefaults(): void
    {
        $block = $this->createBlock($this->createStub(MediaFacade::class));

        $this->assertSame([
            'mediaId' => null,
            'alt' => '',
            'position' => 'center',
            'link' => null,
        ], $block->getDefaultData());
        $this->assertFalse(ContentEditorTestFactory::getField($block, 'mediaId')->isRequired());
    }

    public function testExportOfExistingMediaAddsUrlAndDimensions(): void
    {
        $media = $this->createStub(Media::class);
        $media->method('getWidth')->willReturn(800);
        $media->method('getHeight')->willReturn(600);
        $mediaFacade = $this->createStub(MediaFacade::class);
        $mediaFacade->method('getById')->willReturn($media);
        $mediaFacade->method('getImageUrl')->willReturn('https://example.com/image.jpg');

        $result = $this->createBlock($mediaFacade)->export([
            'mediaId' => self::MEDIA_ID,
            'url' => '/legacy.png',
        ], new ExportContext(1, 'en'));

        $this->assertSame(
            [
                'mediaId' => self::MEDIA_ID,
                'url' => 'https://example.com/image.jpg',
                'width' => 800,
                'height' => 600,
            ],
            $result,
        );
    }

    public function testExportOfLegacyImageKeepsUrlAndMarksItLegacy(): void
    {
        $result = $this->createBlock($this->createStub(MediaFacade::class))->export([
            'mediaId' => null,
            'url' => '/legacy.png',
        ], new ExportContext(1, 'en'));

        $this->assertSame([
            'mediaId' => null,
            'url' => '/legacy.png',
            'legacy' => true,
        ], $result);
    }

    public function testExportOfDeletedMediaFallsBackToStoredUrl(): void
    {
        $mediaFacade = $this->createStub(MediaFacade::class);
        $mediaFacade->method('getById')->willThrowException(new MediaNotFoundException());

        $result = $this->createBlock($mediaFacade)->export([
            'mediaId' => self::MEDIA_ID,
            'url' => '/legacy.png',
        ], new ExportContext(1, 'en'));

        $this->assertSame('/legacy.png', $result['url']);
        $this->assertTrue($result['legacy']);
    }

    public function testExportWithoutMediaAndUrlHasNullUrl(): void
    {
        $result = $this->createBlock($this->createStub(MediaFacade::class))->export(['mediaId' => null], new ExportContext(1, 'en'));

        $this->assertSame(['mediaId' => null, 'url' => null], $result);
    }

    private function createBlock(MediaFacade $mediaFacade): ImageBlock
    {
        $domain = $this->createStub(Domain::class);
        $domain->method('getDomainConfigById')->willReturn($this->createStub(DomainConfig::class));

        return new ImageBlock($mediaFacade, $domain);
    }
}
