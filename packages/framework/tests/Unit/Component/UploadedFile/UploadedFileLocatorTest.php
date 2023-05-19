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
    public function testFileExists()
    {
        $uploadedFileMock = $this->getMockBuilder(UploadedFile::class)
            ->setMethods(['getFilename', 'getEntityName'])
            ->disableOriginalConstructor()
            ->getMock();
        $uploadedFileMock->method('getFilename')->willReturn('dummy.txt');
        $uploadedFileMock->method('getEntityName')->willReturn('entityName');

        $uploadedFileLocator = $this->createUploadedFileLocator();
        $this->assertTrue($uploadedFileLocator->fileExists($uploadedFileMock));
    }

    public function testFileNotExists()
    {
        $uploadedFileMock = $this->getMockBuilder(UploadedFile::class)
            ->setMethods(['getFilename', 'getEntityName'])
            ->disableOriginalConstructor()
            ->getMock();
        $uploadedFileMock->method('getFilename')->willReturn('non-existent.txt');
        $uploadedFileMock->method('getEntityName')->willReturn('entityName');

        $uploadedFileLocator = $this->createUploadedFileLocator(false);
        $this->assertFalse($uploadedFileLocator->fileExists($uploadedFileMock));
    }

    public function testGetAbsoluteFilePath()
    {
        $uploadedFileDir = __DIR__ . '/UploadedFileLocatorData/';

        $uploadedFileLocator = $this->createUploadedFileLocator();
        $this->assertSame(
            $uploadedFileDir . 'entityName',
            $uploadedFileLocator->getAbsoluteFilePath('entityName'),
        );
    }

    public function testGetAbsoluteUploadedFileFilepath()
    {
        $uploadedFileDir = __DIR__ . '/UploadedFileLocatorData/';
        $uploadedFileMock = $this->getMockBuilder(UploadedFile::class)
            ->setMethods(['getFilename', 'getEntityName'])
            ->disableOriginalConstructor()
            ->getMock();
        $uploadedFileMock->method('getFilename')->willReturn('dummy.txt');
        $uploadedFileMock->method('getEntityName')->willReturn('entityName');

        $uploadedFileLocator = $this->createUploadedFileLocator();
        $this->assertSame(
            $uploadedFileDir . 'entityName/dummy.txt',
            $uploadedFileLocator->getAbsoluteUploadedFileFilepath($uploadedFileMock),
        );
    }

    public function testGetRelativeUploadedFileFilepath()
    {
        $uploadedFileMock = $this->getMockBuilder(UploadedFile::class)
            ->setMethods(['getFilename', 'getEntityName'])
            ->disableOriginalConstructor()
            ->getMock();
        $uploadedFileMock->method('getFilename')->willReturn('dummy.txt');
        $uploadedFileMock->method('getEntityName')->willReturn('entityName');

        $uploadedFileLocator = $this->createUploadedFileLocator();
        $this->assertSame(
            'entityName/dummy.txt',
            $uploadedFileLocator->getRelativeUploadedFileFilepath($uploadedFileMock),
        );
    }

    /**
     * @param bool $has
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator
     */
    private function createUploadedFileLocator($has = true)
    {
        $uploadedFileDir = __DIR__ . '/UploadedFileLocatorData/';

        $filesystemMock = $this->createMock(FilesystemOperator::class);
        $filesystemMock->method('has')->willReturn($has);

        $domainRouterFactoryMock = $this->getMockBuilder(DomainRouterFactory::class)
            ->setMethods(['__construct', 'getRouter'])
            ->disableOriginalConstructor()
            ->getMock();

        return new UploadedFileLocator($uploadedFileDir, $filesystemMock, $domainRouterFactoryMock);
    }
}
