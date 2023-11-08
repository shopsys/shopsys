<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image\Config;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateEntityNameException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateTypeNameException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\EntityParseException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigDefinition;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader;
use Symfony\Component\Filesystem\Filesystem;

class ImageConfigLoaderTest extends TestCase
{
    private ImageConfigLoader $imageConfigLoader;

    protected function setUp(): void
    {
        $filesystem = new Filesystem();
        $entityNameResolver = new EntityNameResolver([]);
        $this->imageConfigLoader = new ImageConfigLoader($filesystem, $entityNameResolver);
    }

    public function testLoadFromArrayDuplicateEntityName(): void
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_TYPES => [],
            ],
            [
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_CLASS => 'Class_2',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_TYPES => [],
            ],
        ];

        $previousException = null;

        try {
            $this->imageConfigLoader->loadFromArray($inputConfig);
        } catch (EntityParseException $exception) {
            $previousException = $exception->getPrevious();
        }

        $this->assertInstanceOf(DuplicateEntityNameException::class, $previousException);
    }

    public function testLoadFromArrayDuplicateEntityClass(): void
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_TYPES => [],
            ],
            [
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_2',
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_TYPES => [],
            ],
        ];

        $previousException = null;

        try {
            $this->imageConfigLoader->loadFromArray($inputConfig);
        } catch (EntityParseException $exception) {
            $previousException = $exception->getPrevious();
        }

        $this->assertInstanceOf(DuplicateEntityNameException::class, $previousException);
    }

    public function testLoadFromArrayDuplicateTypeName(): void
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_TYPES => [
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_1',
                        ImageConfigDefinition::CONFIG_MULTIPLE => false,
                    ],
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_1',
                        ImageConfigDefinition::CONFIG_MULTIPLE => false,
                    ],
                ],
            ],
        ];

        $previousException = null;

        try {
            $this->imageConfigLoader->loadFromArray($inputConfig);
        } catch (EntityParseException $exception) {
            $previousException = $exception->getPrevious();
        }

        $this->assertInstanceOf(DuplicateTypeNameException::class, $previousException);
    }

    public function testLoadFromArray(): void
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_TYPES => [
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_1',
                        ImageConfigDefinition::CONFIG_MULTIPLE => true,
                    ],
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_2',
                        ImageConfigDefinition::CONFIG_MULTIPLE => false,
                    ],
                ],
            ],
        ];

        $preparedConfig = $this->imageConfigLoader->loadFromArray($inputConfig);

        $imageEntityConfig = $preparedConfig[$inputConfig[0][ImageConfigDefinition::CONFIG_CLASS]];
        $this->assertSame('Class_1', $imageEntityConfig->getEntityClass());
        $this->assertSame('Name_1', $imageEntityConfig->getEntityName());
        $this->assertFalse($imageEntityConfig->isMultiple(null));
        $this->assertTrue($imageEntityConfig->isMultiple('TypeName_1'));
        $this->assertFalse($imageEntityConfig->isMultiple('TypeName_2'));
    }
}
