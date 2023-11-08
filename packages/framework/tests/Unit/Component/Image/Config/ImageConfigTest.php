<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image\Config;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
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
    private function getBaseImageConfig(): ImageConfig
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => stdClass::class,
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_TYPES => [
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_1',
                        ImageConfigDefinition::CONFIG_MULTIPLE => false,
                    ],
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_2',
                        ImageConfigDefinition::CONFIG_MULTIPLE => false,
                    ],
                ],
                [
                    ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                    ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_2',
                    ImageConfigDefinition::CONFIG_TYPES => [],
                ],
            ],
        ];

        $filesystem = new Filesystem();
        $entityNameResolver = new EntityNameResolver([]);
        $imageConfigLoader = new ImageConfigLoader($filesystem, $entityNameResolver);
        $imageEntityConfigByClass = $imageConfigLoader->loadFromArray($inputConfig);

        return new ImageConfig($imageEntityConfigByClass, $entityNameResolver);
    }

    public function testGetEntityName(): void
    {
        $imageConfig = $this->getBaseImageConfig();
        $entity = new stdClass();

        $this->assertSame('Name_1', $imageConfig->getEntityName($entity));
    }

    public function testGetEntityNameNotFound(): void
    {
        $imageConfig = $this->getBaseImageConfig();

        $this->expectException(ImageEntityConfigNotFoundException::class);
        $imageConfig->getEntityName($this);
    }

    public function tesGetImageEntityConfig(): void
    {
        $imageConfig = $this->getBaseImageConfig();
        $entity = new stdClass();

        $imageEntityConfig = $imageConfig->getImageEntityConfig($entity);
        $this->assertSame('Name_1', $imageEntityConfig->getEntityName());
    }

    public function tesGetImageEntityConfigNotFound(): void
    {
        $imageConfig = $this->getBaseImageConfig();

        $this->expectException(ImageEntityConfigNotFoundException::class);
        $imageConfig->getImageEntityConfig($this);
    }
}
