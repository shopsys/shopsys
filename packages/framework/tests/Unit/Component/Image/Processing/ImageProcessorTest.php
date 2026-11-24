<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image\Processing;

use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;

class ImageProcessorTest extends TestCase
{
    public function testPngIsPreservedForStorageAndPreview(): void
    {
        $encodedPng = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAAAAAA6fptVAAAACklEQVR4nGNgAAAAAgABSK+kcQAAAABJRU5ErkJggg==';
        $png = base64_decode($encodedPng, true);
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->method('read')->with('tmp/logo.png')->willReturn($png);
        $filesystem->method('mimeType')->willReturn('image/png');
        $filesystem->expects($this->once())->method('write')->with('tmp/logo.png', $png);
        $processor = new ImageProcessor($filesystem);

        $this->assertSame('data:image/png;base64,' . $encodedPng, $processor->getEncodedImageUri('tmp/logo.png'));
        $this->assertSame('logo.png', $processor->convertToShopFormatAndGetNewFilename('tmp/logo.png'));
    }

    public function testRasterExceedingPixelLimitIsRejected(): void
    {
        $twoByTwoPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAAAAABX3VL4AAAAC0lEQVR4nGNgAAEAAAYAAf6MZ8gAAAAASUVORK5CYII=', true);
        $processor = new class($this->createStub(FilesystemOperator::class)) extends ImageProcessor {
            protected const int MAX_RASTER_PIXELS = 3;
        };

        $this->assertTrue(ImageProcessor::isValidImageContent($twoByTwoPng, 'png'));
        $this->assertFalse($processor::isValidImageContent($twoByTwoPng, 'png'));
    }

    public function testSafeSvgIsPreservedForStorageAndPreview(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><circle r="8"/></svg>';
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->method('read')->with('tmp/logo.SVG')->willReturn($svg);
        $filesystem->method('mimeType')->willReturn('image/svg+xml');
        $processor = new ImageProcessor($filesystem);

        $this->assertSame('data:image/svg+xml;base64,' . base64_encode($svg), $processor->getEncodedImageUri('tmp/logo.SVG'));
        $filesystem->expects($this->once())->method('write')->with('tmp/logo.svg', $svg);
        $this->assertSame('logo.svg', $processor->convertToShopFormatAndGetNewFilename('tmp/logo.SVG'));
    }

    #[TestWith(['image/svg+xml'])]
    #[TestWith(['image/png'])]
    public function testSvgWithRasterExtensionIsRejectedForPreview(string $mimeType): void
    {
        $filesystem = $this->createStub(FilesystemOperator::class);
        $filesystem->method('mimeType')->willReturn($mimeType);
        $filesystem->method('read')->willReturn('<svg xmlns="http://www.w3.org/2000/svg"/>');

        $this->expectException(FileIsNotSupportedImageException::class);
        (new ImageProcessor($filesystem))->getEncodedImageUri('tmp/logo.png');
    }

    public function testInvalidSvgIsRejectedBeforeWriting(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->method('read')->willReturn('<svg xmlns="http://www.w3.org/2000/svg"><foreignObject/></svg>');
        $filesystem->expects($this->never())->method('write');
        $filesystem->expects($this->never())->method('delete');

        $this->expectException(FileIsNotSupportedImageException::class);
        (new ImageProcessor($filesystem))->convertToShopFormatAndGetNewFilename('tmp/logo.svg');
    }

    public function testSvgWithRasterExtensionIsRejectedBeforeStorage(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->method('mimeType')->willReturn('image/png');
        $filesystem->method('read')->willReturn('<svg xmlns="http://www.w3.org/2000/svg"/>');
        $filesystem->expects($this->never())->method('delete');
        $filesystem->expects($this->never())->method('write');

        $this->expectException(FileIsNotSupportedImageException::class);
        (new ImageProcessor($filesystem))->convertToShopFormatAndGetNewFilename('tmp/logo.png');
    }
}
