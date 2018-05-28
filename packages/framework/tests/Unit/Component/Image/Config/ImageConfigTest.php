<?php

namespace Tests\FrameworkBundle\Unit\Component\Image\Config;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigDefinition;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;

class ImageConfigTest extends TestCase
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig
     */
    private function getBaseImageConfig()
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => stdClass::class,
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_SIZES => [
                    [
                        ImageConfigDefinition::CONFIG_SIZE_NAME => 'SizeName_0_1',
                        ImageConfigDefinition::CONFIG_SIZE_WIDTH => null,
                        ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                        ImageConfigDefinition::CONFIG_SIZE_CROP => false,
                        ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                    ],
                ],
                ImageConfigDefinition::CONFIG_TYPES => [
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_1',
                        ImageConfigDefinition::CONFIG_MULTIPLE => false,
                        ImageConfigDefinition::CONFIG_SIZES => [
                            [
                                ImageConfigDefinition::CONFIG_SIZE_NAME => 'SizeName_1_1',
                                ImageConfigDefinition::CONFIG_SIZE_WIDTH => null,
                                ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                                ImageConfigDefinition::CONFIG_SIZE_CROP => false,
                                ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                            ],
                            [
                                ImageConfigDefinition::CONFIG_SIZE_NAME => null,
                                ImageConfigDefinition::CONFIG_SIZE_WIDTH => 200,
                                ImageConfigDefinition::CONFIG_SIZE_HEIGHT => 100,
                                ImageConfigDefinition::CONFIG_SIZE_CROP => true,
                                ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                            ],
                        ],
                    ],
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_2',
                        ImageConfigDefinition::CONFIG_MULTIPLE => false,
                        ImageConfigDefinition::CONFIG_SIZES => [],
                    ],
                ],
                [
                    ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                    ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_2',
                    ImageConfigDefinition::CONFIG_SIZES => [],
                    ImageConfigDefinition::CONFIG_TYPES => [],
                ],
            ],
        ];

        $filesystem = new Filesystem();
        $imageConfigLoader = new ImageConfigLoader($filesystem);
        $imageEntityConfigByClass = $imageConfigLoader->loadFromArray($inputConfig);

        return new ImageConfig($imageEntityConfigByClass);
    }

    public function testGetEntityName()
    {
        $imageConfig = $this->getBaseImageConfig();
        $entity = new stdClass();

        $this->assertSame('Name_1', $imageConfig->getEntityName($entity));
    }

    public function testGetEntityNameNotFound()
    {
        $imageConfig = $this->getBaseImageConfig();

        $this->expectException(ImageEntityConfigNotFoundException::class);
        $imageConfig->getEntityName($this);
    }

    public function testGetImageSizeConfigByEntity()
    {
        $imageConfig = $this->getBaseImageConfig();
        $entity = new stdClass();

        $imageSizeConfig1 = $imageConfig->getImageSizeConfigByEntity($entity, 'TypeName_1', 'SizeName_1_1');
        $this->assertSame('SizeName_1_1', $imageSizeConfig1->getName());

        $imageSizeConfig2 = $imageConfig->getImageSizeConfigByEntity($entity, 'TypeName_1', null);
        $this->assertNull($imageSizeConfig2->getName());
        $this->assertSame(200, $imageSizeConfig2->getWidth());
        $this->assertSame(100, $imageSizeConfig2->getHeight());
        $this->assertTrue($imageSizeConfig2->getCrop());

        $imageSizeConfig3 = $imageConfig->getImageSizeConfigByEntity($entity, null, 'SizeName_0_1');
        $this->assertSame('SizeName_0_1', $imageSizeConfig3->getName());
    }

    public function testGetImageSizeConfigByEntityName()
    {
        $imageConfig = $this->getBaseImageConfig();
        $entityName = 'Name_1';

        $imageSizeConfig1 = $imageConfig->getImageSizeConfigByEntityName($entityName, 'TypeName_1', 'SizeName_1_1');
        $this->assertSame('SizeName_1_1', $imageSizeConfig1->getName());

        $imageSizeConfig2 = $imageConfig->getImageSizeConfigByEntityName($entityName, 'TypeName_1', null);
        $this->assertNull($imageSizeConfig2->getName());
        $this->assertSame(200, $imageSizeConfig2->getWidth());
        $this->assertSame(100, $imageSizeConfig2->getHeight());
        $this->assertTrue($imageSizeConfig2->getCrop());

        $imageSizeConfig3 = $imageConfig->getImageSizeConfigByEntityName($entityName, null, 'SizeName_0_1');
        $this->assertSame('SizeName_0_1', $imageSizeConfig3->getName());
    }

    public function tesGetImageEntityConfig()
    {
        $imageConfig = $this->getBaseImageConfig();
        $entity = new stdClass();

        $imageEntityConfig = $imageConfig->getImageEntityConfig($entity);
        $this->assertSame('Name_1', $imageEntityConfig->getEntityName());
    }

    public function tesGetImageEntityConfigNotFound()
    {
        $imageConfig = $this->getBaseImageConfig();

        $this->expectException(ImageEntityConfigNotFoundException::class);
        $imageConfig->getImageEntityConfig($this);
    }
}
