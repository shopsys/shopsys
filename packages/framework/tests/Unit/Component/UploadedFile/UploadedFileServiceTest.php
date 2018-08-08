<?php

namespace Tests\FrameworkBundle\Unit\Component\UploadedFile;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileService;

class UploadedFileServiceTest extends TestCase
{
    public function testCreateUploadedFile(): void
    {
        $temporaryFilename = 'temporaryFilename.tmp';
        $temporaryFilenames = [$temporaryFilename];
        $temporaryFilepath = 'path/' . $temporaryFilename;
        $entityId = 1;
        $entityName = 'entityName';
        $entityClass = 'entityClass';

        $fileUploadMock = $this->getMockBuilder(FileUpload::class)
            ->setMethods(['getTemporaryFilePath'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploadMock
            ->expects($this->once())
            ->method('getTemporaryFilePath')
            ->with($this->equalTo($temporaryFilename))
            ->willReturn($temporaryFilepath);

        $uploadedFileEntityConfig = new UploadedFileEntityConfig($entityName, $entityClass);

        $uploadedFileService = new UploadedFileService($fileUploadMock, new UploadedFileFactory());
        $uploadedFile = $uploadedFileService->createUploadedFile($uploadedFileEntityConfig, $entityId, $temporaryFilenames);
        $filesForUpload = $uploadedFile->getTemporaryFilesForUpload();
        $fileForUpload = array_pop($filesForUpload);
        /* @var $fileForUpload \Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload */

        $this->assertSame($entityId, $uploadedFile->getEntityId());
        $this->assertSame($entityName, $uploadedFile->getEntityName());
        $this->assertSame($temporaryFilename, $fileForUpload->getTemporaryFilename());
        $this->assertFalse($fileForUpload->isImage());
    }
}
