<?php

namespace Tests\FrameworkBundle\Unit\Component\UploadedFile\Config;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\UploadedFileEntityConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\UploadedFileTypeConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Tests\FrameworkBundle\Unit\Component\UploadedFile\Dummy;

class UploadedFileConfigTest extends TestCase
{
    public function testGetEntityName()
    {
        $entity = new Dummy();
        $uploadedFileConfig = $this->getUploadedFileConfig();

        $this->assertSame('entityName', $uploadedFileConfig->getEntityName($entity));
    }

    public function testGetEntityNameNotFoundException()
    {
        $entity = new Dummy();
        $fileEntityConfigsByClass = [];
        $uploadedFileConfig = new UploadedFileConfig($fileEntityConfigsByClass);

        $this->expectException(
            UploadedFileEntityConfigNotFoundException::class,
        );
        $uploadedFileConfig->getEntityName($entity);
    }

    public function testGetAllUploadedFileEntityConfigs()
    {
        $fileEntityConfigsByClass = $this->getFileEntityConfigsByClass();
        $uploadedFileConfig = new UploadedFileConfig($fileEntityConfigsByClass);

        $this->assertSame($fileEntityConfigsByClass, $uploadedFileConfig->getAllUploadedFileEntityConfigs());
    }

    public function testGetUploadedFileEntityConfig()
    {
        $entity = new Dummy();
        $fileEntityConfigsByClass = [];
        $uploadedFileConfig = new UploadedFileConfig($fileEntityConfigsByClass);

        $this->expectException(
            UploadedFileEntityConfigNotFoundException::class,
        );
        $uploadedFileConfig->getUploadedFileEntityConfig($entity);
    }

    public function testHasUploadedFileEntityConfig()
    {
        $entity = new Dummy();
        $uploadedFileConfig = $this->getUploadedFileConfig();

        $this->assertTrue($uploadedFileConfig->hasUploadedFileEntityConfig($entity));
    }

    public function testHasNotUploadedFileEntityConfig()
    {
        $entity = new Dummy();
        $fileEntityConfigsByClass = [];
        $uploadedFileConfig = new UploadedFileConfig($fileEntityConfigsByClass);

        $this->assertFalse($uploadedFileConfig->hasUploadedFileEntityConfig($entity));
    }

    public function testGetNotExistingUploadedFileTypeConfig()
    {
        $entity = new Dummy();
        $uploadedFileConfig = $this->getUploadedFileConfig();
        $uploadedFileEntityConfig = $uploadedFileConfig->getUploadedFileEntityConfig($entity);

        $this->expectException(
            UploadedFileTypeConfigNotFoundException::class,
        );

        $uploadedFileEntityConfig->getTypeByName('test');
    }

    public function testTypesHaveRightMultipleSet()
    {
        $entity = new Dummy();
        $uploadedFileConfig = $this->getUploadedFileConfig();
        $uploadedFileEntityConfig = $uploadedFileConfig->getUploadedFileEntityConfig($entity);
        $uploadedFileTypeConfig1 = $uploadedFileEntityConfig->getTypeByName('default');
        $uploadedFileTypeConfig2 = $uploadedFileEntityConfig->getTypeByName('additional');

        $this->assertFalse($uploadedFileTypeConfig1->isMultiple());
        $this->assertTrue($uploadedFileTypeConfig2->isMultiple());
    }

    /**
     * @return array
     */
    private function getFileEntityConfigsByClass(): array
    {
        $uploadedFileType1 = new UploadedFileTypeConfig('default', false);
        $uploadedFileType2 = new UploadedFileTypeConfig('additional', true);

        $uploadedFileTypes = ['default' => $uploadedFileType1, 'additional' => $uploadedFileType2];

        $uploadedFileEntityConfig = new UploadedFileEntityConfig('entityName', Dummy::class, $uploadedFileTypes);

        return [
            Dummy::class => $uploadedFileEntityConfig,
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig
     */
    private function getUploadedFileConfig(): UploadedFileConfig
    {
        $fileEntityConfigsByClass = $this->getFileEntityConfigsByClass();

        return new UploadedFileConfig($fileEntityConfigsByClass);
    }
}
