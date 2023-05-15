<?php

namespace Tests\FrameworkBundle\Unit\Component\UploadedFile;

use Doctrine\ORM\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDeleteDoctrineListener;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;

class UploadedFileDeleteDoctrineListenerTest extends TestCase
{
    public function testPreRemoveDeleteFile()
    {
        $uploadedFile = new UploadedFile('entityName', 1, 'default', 'dummy.txt', 'dummy.txt', 0);

        $uploadedFileConfig = new UploadedFileConfig([]);

        $uploadedFileFacadeMock = $this->getMockBuilder(UploadedFileFacade::class)
            ->setMethods(['deleteFileFromFilesystem'])
            ->disableOriginalConstructor()
            ->getMock();
        $uploadedFileFacadeMock->expects($this->once())->method('deleteFileFromFilesystem')->with(
            $this->equalTo($uploadedFile),
        );

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

        $uploadedFileTypeConfig = new UploadedFileTypeConfig('default', false);

        $uploadedFileEntityConfig = new UploadedFileEntityConfig(
            'entityName',
            Dummy::class,
            ['default' => $uploadedFileTypeConfig],
        );
        $uploadedFileConfig = new UploadedFileConfig([
            Dummy::class => $uploadedFileEntityConfig,
        ]);

        $uploadedFileFacadeMock = $this->getMockBuilder(UploadedFileFacade::class)
            ->setMethods(['deleteAllUploadedFilesByEntity'])
            ->disableOriginalConstructor()
            ->getMock();
        $uploadedFileFacadeMock
            ->expects($this->once())
            ->method('deleteAllUploadedFilesByEntity')
            ->with($this->equalTo($entity));

        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->setMethods(['getEntity', 'getEntityManager'])
            ->disableOriginalConstructor()
            ->getMock();
        $args->method('getEntity')->willReturn($entity);

        $doctrineListener = new UploadedFileDeleteDoctrineListener($uploadedFileConfig, $uploadedFileFacadeMock);
        $doctrineListener->preRemove($args);
    }
}
