<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\UploadedFile;

use Doctrine\ORM\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDeleteDoctrineListener;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;

class UploadedFileDeleteDoctrineListenerTest extends TestCase
{
    public function testPreRemoveDeleteFile()
    {
        $uploadedFile = new UploadedFile('dummy.txt', 'dummy.txt', ['en' => 'dummy']);

        $uploadedFileConfig = new UploadedFileConfig([]);

        $uploadedFileFacadeMock = $this->getMockBuilder(UploadedFileFacade::class)
            ->onlyMethods(['deleteFileFromFilesystem'])
            ->disableOriginalConstructor()
            ->getMock();
        $uploadedFileFacadeMock->expects($this->once())->method('deleteFileFromFilesystem')->with(
            $this->equalTo($uploadedFile),
        );

        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->onlyMethods(['getEntity'])
            ->disableOriginalConstructor()
            ->getMock();
        $args->method('getEntity')->willReturn($uploadedFile);

        $doctrineListener = new UploadedFileDeleteDoctrineListener($uploadedFileConfig, $uploadedFileFacadeMock);

        $doctrineListener->preRemove($args);
    }
}
