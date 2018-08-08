<?php

namespace Tests\FrameworkBundle\Unit\Component\UploadedFile\Config;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig;
use Tests\FrameworkBundle\Unit\Component\UploadedFile\Dummy;

class UploadedFileConfigTest extends TestCase
{
    public function testGetEntityName(): void
    {
        $entity = new Dummy();
        $fileEntityConfigsByClass = [
            Dummy::class => new UploadedFileEntityConfig('entityName', Dummy::class),
        ];
        $uploadedFileConfig = new UploadedFileConfig($fileEntityConfigsByClass);

        $this->assertSame('entityName', $uploadedFileConfig->getEntityName($entity));
    }

    public function testGetEntityNameNotFoundException(): void
    {
        $entity = new Dummy();
        $fileEntityConfigsByClass = [];
        $uploadedFileConfig = new UploadedFileConfig($fileEntityConfigsByClass);

        $this->expectException(
            \Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\UploadedFileEntityConfigNotFoundException::class
        );
        $uploadedFileConfig->getEntityName($entity);
    }

    public function testGetAllUploadedFileEntityConfigs(): void
    {
        $fileEntityConfigsByClass = [
            Dummy::class => new UploadedFileEntityConfig('entityName', Dummy::class),
        ];
        $uploadedFileConfig = new UploadedFileConfig($fileEntityConfigsByClass);

        $this->assertSame($fileEntityConfigsByClass, $uploadedFileConfig->getAllUploadedFileEntityConfigs());
    }

    public function testGetUploadedFileEntityConfig(): void
    {
        $entity = new Dummy();
        $fileEntityConfigsByClass = [];
        $uploadedFileConfig = new UploadedFileConfig($fileEntityConfigsByClass);

        $this->expectException(
            \Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\UploadedFileEntityConfigNotFoundException::class
        );
        $uploadedFileConfig->getUploadedFileEntityConfig($entity);
    }

    public function testHasUploadedFileEntityConfig(): void
    {
        $entity = new Dummy();
        $fileEntityConfigsByClass = [
            Dummy::class => new UploadedFileEntityConfig('entityName', Dummy::class),
        ];
        $uploadedFileConfig = new UploadedFileConfig($fileEntityConfigsByClass);

        $this->assertTrue($uploadedFileConfig->hasUploadedFileEntityConfig($entity));
    }

    public function testHasNotUploadedFileEntityConfig(): void
    {
        $entity = new Dummy();
        $fileEntityConfigsByClass = [];
        $uploadedFileConfig = new UploadedFileConfig($fileEntityConfigsByClass);

        $this->assertFalse($uploadedFileConfig->hasUploadedFileEntityConfig($entity));
    }
}
