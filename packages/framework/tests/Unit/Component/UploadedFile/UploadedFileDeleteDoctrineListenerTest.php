<?php

namespace Tests\FrameworkBundle\Unit\Component\UploadedFile;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDeleteDoctrineListener;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;

class UploadedFileDeleteDoctrineListenerTest extends TestCase
{
    public function testPreRemoveDeleteFile()
    {
        $uploadedFile = new UploadedFile('entityName', 1, 'dummy.txt');

        $uploadedFileConfig = new UploadedFileConfig([]);

        $uploadedFileFacadeMock = $this->getMockBuilder(UploadedFileFacade::class)
            ->setMethods(['deleteFileFromFilesystem'])
            ->disableOriginalConstructor()
            ->getMock();
        $uploadedFileFacadeMock->expects($this->once())->method('deleteFileFromFilesystem')->with($this->equalTo($uploadedFile));

        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->setMethods(['getEntity'])
            ->disableOriginalConstructor()
            ->getMock();
        $args->method('getEntity')->willReturn($uploadedFile);

        $doctrineListener = new UploadedFileDeleteDoctrineListener($uploadedFileConfig, $uploadedFileFacadeMock);

        $doctrineListener->preRemove($args);
    }

    public function testPreRemoveDeleteUploadedFile()
    {
        $entity = new Dummy();
        $uploadedFile = new UploadedFile('entitzId', 1, 'dummy.txt');

        $uploadedFileEntityConfig = new UploadedFileEntityConfig('entityName', Dummy::class);
        $uploadedFileConfig = new UploadedFileConfig([
            Dummy::class => $uploadedFileEntityConfig,
        ]);

        $uploadedFileFacadeMock = $this->getMockBuilder(UploadedFileFacade::class)
            ->setMethods(['findUploadedFileByEntity'])
            ->disableOriginalConstructor()
            ->getMock();
        $uploadedFileFacadeMock
            ->expects($this->once())
            ->method('findUploadedFileByEntity')
            ->with($this->equalTo($entity))
            ->willReturn($uploadedFile);

        $emMock = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()
            ->getMock();
        $emMock->expects($this->once())->method('remove')->with($uploadedFile);

        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->setMethods(['getEntity', 'getEntityManager'])
            ->disableOriginalConstructor()
            ->getMock();
        $args->method('getEntity')->willReturn($entity);
        $args->method('getEntityManager')->willReturn($emMock);

        $doctrineListener = new UploadedFileDeleteDoctrineListener($uploadedFileConfig, $uploadedFileFacadeMock);
        $doctrineListener->preRemove($args);
    }
}
