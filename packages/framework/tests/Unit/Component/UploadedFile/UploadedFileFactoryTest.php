<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\UploadedFile;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactory;

class UploadedFileFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $temporaryFilename = 'temporaryFilename.tmp';
        $temporaryFilepath = 'path/' . $temporaryFilename;

        $fileUploadMock = $this->getMockBuilder(FileUpload::class)
            ->onlyMethods(['getTemporaryFilePath', 'getTemporaryFilesize'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploadMock
            ->expects($this->once())
            ->method('getTemporaryFilePath')
            ->with($this->equalTo($temporaryFilename))
            ->willReturn($temporaryFilepath);
        $fileUploadMock
            ->expects($this->once())
            ->method('getTemporaryFilesize')
            ->with($this->equalTo($temporaryFilename))
            ->willReturn(0);

        $uploadedFileFactory = new UploadedFileFactory($fileUploadMock, new EntityNameResolver([]));
        $name = 'test-name';
        $nameLocale = 'en';

        $uploadedFile = $uploadedFileFactory->create($temporaryFilename, 'Příliš žluťoučký Kůň.png', [$nameLocale => $name]);
        $filesForUpload = $uploadedFile->getTemporaryFilesForUpload();
        /** @var \Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload $fileForUpload */
        $fileForUpload = array_pop($filesForUpload);
        $this->assertSame($temporaryFilename, $fileForUpload->getTemporaryFilename());
        $this->assertSame($name, $uploadedFile->getTranslatedName($nameLocale));
        $this->assertSame('prilis-zlutoucky-kun', $uploadedFile->getSlug());
        $this->assertSame(UploadedFile::class, $fileForUpload->getFileClass());
    }
}
