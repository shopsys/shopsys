<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Twig\FileThumbnail;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Image\Processing\Exception\FileIsNotSupportedImageException;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;
use Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailExtension;

class FileThumbnailExtensionTest extends TestCase
{
    public function testGetFileThumbnailInfoByTemporaryFilenameBrokenImage(): void
    {
        $temporaryFilename = 'filename.jpg';

        $fileUploadMock = $this->getMockBuilder(FileUpload::class)
            ->onlyMethods(['getTemporaryFilepath'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploadMock->expects($this->any())->method('getTemporaryFilepath')->willReturn(
            'dir/' . $temporaryFilename,
        );

        $exception = new FileIsNotSupportedImageException($temporaryFilename);
        $imageProcessorMock = $this->getMockBuilder(ImageProcessor::class)
            ->onlyMethods(['getEncodedImageUri'])
            ->disableOriginalConstructor()
            ->getMock();
        $imageProcessorMock->expects($this->once())->method('getEncodedImageUri')->willThrowException(
            $exception,
        );

        $fileThumbnailExtension = new FileThumbnailExtension($fileUploadMock, $imageProcessorMock);
        $fileThumbnailInfo = $fileThumbnailExtension->getFileThumbnailInfoByTemporaryFilename($temporaryFilename);

        $this->assertSame(FileThumbnailExtension::DEFAULT_ICON_TYPE, $fileThumbnailInfo->getIconType());
        $this->assertNull($fileThumbnailInfo->getImageUri());
    }

    public function testGetFileThumbnailInfoByTemporaryFilenameImage(): void
    {
        $temporaryFilename = 'filename.jpg';
        $encodedData = 'encodedData';

        $fileUploadMock = $this->getMockBuilder(FileUpload::class)
            ->onlyMethods(['getTemporaryFilepath'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploadMock->expects($this->any())->method('getTemporaryFilepath')->willReturn(
            'dir/' . $temporaryFilename,
        );

        $imageProcessorMock = $this->getMockBuilder(ImageProcessor::class)
            ->onlyMethods(['getEncodedImageUri'])
            ->disableOriginalConstructor()
            ->getMock();
        $imageProcessorMock->expects($this->once())->method('getEncodedImageUri')->willReturn(
            $encodedData,
        );

        $fileThumbnailExtension = new FileThumbnailExtension($fileUploadMock, $imageProcessorMock);
        $fileThumbnailInfo = $fileThumbnailExtension->getFileThumbnailInfoByTemporaryFilename($temporaryFilename);

        $this->assertNull($fileThumbnailInfo->getIconType());
        $this->assertSame($encodedData, $fileThumbnailInfo->getImageUri());
    }

    public function testGetFileThumbnailInfoByTemporaryFilenameImageDocument(): void
    {
        $temporaryFilename = 'filename.doc';

        $fileUploadMock = $this->getMockBuilder(FileUpload::class)
            ->onlyMethods(['getTemporaryFilepath'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploadMock->expects($this->any())->method('getTemporaryFilepath')->willReturn(
            'dir/' . $temporaryFilename,
        );

        $exception = new FileIsNotSupportedImageException($temporaryFilename);
        $imageProcessorMock = $this->getMockBuilder(ImageProcessor::class)
            ->onlyMethods(['getEncodedImageUri'])
            ->disableOriginalConstructor()
            ->getMock();
        $imageProcessorMock->expects($this->once())->method('getEncodedImageUri')->willThrowException(
            $exception,
        );

        $fileThumbnailExtension = new FileThumbnailExtension($fileUploadMock, $imageProcessorMock);
        $fileThumbnailInfo = $fileThumbnailExtension->getFileThumbnailInfoByTemporaryFilename($temporaryFilename);

        $this->assertSame('word', $fileThumbnailInfo->getIconType());
        $this->assertNull($fileThumbnailInfo->getImageUri());
    }
}
