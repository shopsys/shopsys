<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\UploadedFile;

use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator;

class UploadedFileLocatorTest extends TestCase
{
    public function testFileExists(): void
    {
        $uploadedFileStub = $this->createStub(UploadedFile::class);
        $uploadedFileStub->method('getFilename')->willReturn('dummy.txt');

        $uploadedFileLocator = $this->createUploadedFileLocator();
        $this->assertTrue($uploadedFileLocator->fileExists($uploadedFileStub));
    }

    public function testFileNotExists(): void
    {
        $uploadedFileStub = $this->createStub(UploadedFile::class);
        $uploadedFileStub->method('getFilename')->willReturn('non-existent.txt');

        $uploadedFileLocator = $this->createUploadedFileLocator(false);
        $this->assertFalse($uploadedFileLocator->fileExists($uploadedFileStub));
    }

    public function testGetAbsoluteFilePath(): void
    {
        $uploadedFileDir = __DIR__ . '/UploadedFileLocatorData/';

        $uploadedFileLocator = $this->createUploadedFileLocator();
        $this->assertSame(
            $uploadedFileDir . 'entityName',
            $uploadedFileLocator->getAbsoluteFilePath('entityName'),
        );
    }

    public function testGetAbsoluteUploadedFileFilepath(): void
    {
        $uploadedFileDir = __DIR__ . '/UploadedFileLocatorData/';
        $uploadedFileStub = $this->createStub(UploadedFile::class);
        $uploadedFileStub->method('getFilename')->willReturn('dummy.txt');

        $uploadedFileLocator = $this->createUploadedFileLocator();
        $this->assertSame(
            $uploadedFileDir . 'dummy.txt',
            $uploadedFileLocator->getAbsoluteUploadedFileFilepath($uploadedFileStub),
        );
    }

    public function testGetRelativeUploadedFileFilepath(): void
    {
        $uploadedFileStub = $this->createStub(UploadedFile::class);
        $uploadedFileStub->method('getFilename')->willReturn('dummy.txt');

        $uploadedFileLocator = $this->createUploadedFileLocator();
        $this->assertSame(
            'dummy.txt',
            $uploadedFileLocator->getRelativeUploadedFileFilepath($uploadedFileStub),
        );
    }

    private function createUploadedFileLocator(
        bool $has = true,
    ): UploadedFileLocator {
        $uploadedFileDir = __DIR__ . '/UploadedFileLocatorData/';

        $filesystemStub = $this->createStub(FilesystemOperator::class);
        $filesystemStub->method('has')->willReturn($has);

        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);

        return new UploadedFileLocator($uploadedFileDir, $filesystemStub, $domainRouterFactoryStub);
    }
}
