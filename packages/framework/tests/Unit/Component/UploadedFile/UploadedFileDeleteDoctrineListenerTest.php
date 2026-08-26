<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDeleteDoctrineListener;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;

class UploadedFileDeleteDoctrineListenerTest extends TestCase
{
    public function testPreRemoveDeleteFile(): void
    {
        $uploadedFile = new UploadedFile('dummy.txt', 'dummy.txt', 'dummy', ['en' => 'dummy'], 0);

        $uploadedFileFacadeMock = $this->getMockBuilder(UploadedFileFacade::class)
            ->onlyMethods(['deleteFileFromFilesystem'])
            ->disableOriginalConstructor()
            ->getMock();
        $uploadedFileFacadeMock->expects($this->once())->method('deleteFileFromFilesystem')->with(
            $this->equalTo($uploadedFile),
        );

        $entityManagerStub = $this->createStub(EntityManagerInterface::class);
        $args = new PreRemoveEventArgs($uploadedFile, $entityManagerStub);

        $doctrineListener = new UploadedFileDeleteDoctrineListener($uploadedFileFacadeMock);

        $doctrineListener->preRemove($args);
    }
}
