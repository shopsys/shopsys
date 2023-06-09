<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image\Config;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateEntityNameException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateMediaException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateSizeNameException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateTypeNameException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\EntityParseException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\WidthAndHeightMissingException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigDefinition;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig;
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

    public function testLoadFromArrayDuplicateEntityName()
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_SIZES => [],
                ImageConfigDefinition::CONFIG_TYPES => [],
            ],
            [
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_CLASS => 'Class_2',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_SIZES => [],
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

    public function testLoadFromArrayDuplicateEntityClass()
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_SIZES => [],
                ImageConfigDefinition::CONFIG_TYPES => [],
            ],
            [
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_2',
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_SIZES => [],
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

    public function testLoadFromArrayDuplicateNullSizeName()
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_SIZES => [
                    [
                        ImageConfigDefinition::CONFIG_SIZE_NAME => null,
                        ImageConfigDefinition::CONFIG_SIZE_WIDTH => null,
                        ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                        ImageConfigDefinition::CONFIG_SIZE_CROP => false,
                        ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                        ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZES => [],
                    ],
                    [
                        ImageConfigDefinition::CONFIG_SIZE_NAME => null,
                        ImageConfigDefinition::CONFIG_SIZE_WIDTH => null,
                        ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                        ImageConfigDefinition::CONFIG_SIZE_CROP => false,
                        ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                        ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZES => [],
                    ],
                ],
                ImageConfigDefinition::CONFIG_TYPES => [],
            ],
        ];

        $previousException = null;
        try {
            $this->imageConfigLoader->loadFromArray($inputConfig);
        } catch (EntityParseException $exception) {
            $previousException = $exception->getPrevious();
        }

        $this->assertInstanceOf(DuplicateSizeNameException::class, $previousException);
    }

    public function testLoadFromArrayDuplicateTypeName()
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_SIZES => [],
                ImageConfigDefinition::CONFIG_TYPES => [
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_1',
                        ImageConfigDefinition::CONFIG_MULTIPLE => false,
                        ImageConfigDefinition::CONFIG_SIZES => [],
                    ],
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_1',
                        ImageConfigDefinition::CONFIG_MULTIPLE => false,
                        ImageConfigDefinition::CONFIG_SIZES => [],
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

    public function testLoadFromArrayAdditionalSizeWithAndHeightAreNull()
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_SIZES => [],
                ImageConfigDefinition::CONFIG_TYPES => [
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_1',
                        ImageConfigDefinition::CONFIG_MULTIPLE => true,
                        ImageConfigDefinition::CONFIG_SIZES => [
                            [
                                ImageConfigDefinition::CONFIG_SIZE_NAME => 'SizeName_1',
                                ImageConfigDefinition::CONFIG_SIZE_WIDTH => null,
                                ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                                ImageConfigDefinition::CONFIG_SIZE_CROP => false,
                                ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                                ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZES => [
                                    [
                                        ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZE_MEDIA => '(min-width: 1200px)',
                                        ImageConfigDefinition::CONFIG_SIZE_WIDTH => null,
                                        ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                                    ],
                                ],
                            ],
                        ],
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

        $this->assertInstanceOf(WidthAndHeightMissingException::class, $previousException);
    }

    public function testLoadFromArrayAdditionalSizeDuplicateMedia()
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_SIZES => [],
                ImageConfigDefinition::CONFIG_TYPES => [
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_1',
                        ImageConfigDefinition::CONFIG_MULTIPLE => true,
                        ImageConfigDefinition::CONFIG_SIZES => [
                            [
                                ImageConfigDefinition::CONFIG_SIZE_NAME => 'SizeName_1',
                                ImageConfigDefinition::CONFIG_SIZE_WIDTH => null,
                                ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                                ImageConfigDefinition::CONFIG_SIZE_CROP => false,
                                ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                                ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZES => [
                                    [
                                        ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZE_MEDIA => '(min-width: 1200px)',
                                        ImageConfigDefinition::CONFIG_SIZE_WIDTH => 200,
                                        ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                                    ],
                                    [
                                        ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZE_MEDIA => '(min-width: 1200px)',
                                        ImageConfigDefinition::CONFIG_SIZE_WIDTH => 200,
                                        ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                                    ],
                                ],
                            ],
                        ],
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

        $this->assertInstanceOf(DuplicateMediaException::class, $previousException);
    }

    public function testLoadFromArray()
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_SIZES => [],
                ImageConfigDefinition::CONFIG_TYPES => [
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_1',
                        ImageConfigDefinition::CONFIG_MULTIPLE => true,
                        ImageConfigDefinition::CONFIG_SIZES => [
                            [
                                ImageConfigDefinition::CONFIG_SIZE_NAME => 'SizeName_1',
                                ImageConfigDefinition::CONFIG_SIZE_WIDTH => null,
                                ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                                ImageConfigDefinition::CONFIG_SIZE_CROP => false,
                                ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                                ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZES => [
                                    [
                                        ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZE_MEDIA => '(min-width: 1200px)',
                                        ImageConfigDefinition::CONFIG_SIZE_WIDTH => 200,
                                        ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                                    ],
                                ],
                            ],
                            [
                                ImageConfigDefinition::CONFIG_SIZE_NAME => 'SizeName_2',
                                ImageConfigDefinition::CONFIG_SIZE_WIDTH => 200,
                                ImageConfigDefinition::CONFIG_SIZE_HEIGHT => 100,
                                ImageConfigDefinition::CONFIG_SIZE_CROP => true,
                                ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                                ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZES => [],
                            ],
                        ],
                    ],
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_2',
                        ImageConfigDefinition::CONFIG_MULTIPLE => false,
                        ImageConfigDefinition::CONFIG_SIZES => [],
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

        $imageSize = $imageEntityConfig->getSizeConfigByType('TypeName_1', 'SizeName_2');

        $this->assertSame('SizeName_2', $imageSize->getName());
        $this->assertSame(200, $imageSize->getWidth());
        $this->assertSame(100, $imageSize->getHeight());
        $this->assertSame(true, $imageSize->getCrop());

        $imageSize1 = $imageEntityConfig->getSizeConfigByType('TypeName_1', 'SizeName_1');
        $additionalSize = $imageSize1->getAdditionalSize(0);
        $this->assertSame('(min-width: 1200px)', $additionalSize->getMedia());
        $this->assertSame(200, $additionalSize->getWidth());
        $this->assertSame(null, $additionalSize->getHeight());
    }

    public function testLoadFromArrayOriginalSize()
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_SIZES => [],
                ImageConfigDefinition::CONFIG_TYPES => [],
            ],
        ];

        $preparedConfig = $this->imageConfigLoader->loadFromArray($inputConfig);

        $imageEntityConfig = $preparedConfig[$inputConfig[0][ImageConfigDefinition::CONFIG_CLASS]];
        $imageSize = $imageEntityConfig->getSizeConfigByType(null, ImageConfig::ORIGINAL_SIZE_NAME);

        $this->assertInstanceOf(ImageSizeConfig::class, $imageSize);
        $this->assertNull($imageSize->getHeight());
        $this->assertNull($imageSize->getWidth());
        $this->assertFalse($imageSize->getCrop());
    }

    public function testLoadFromArrayExistsOriginalSize()
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => 'Class_1',
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_SIZES => [
                    [
                        ImageConfigDefinition::CONFIG_SIZE_NAME => ImageConfig::ORIGINAL_SIZE_NAME,
                        ImageConfigDefinition::CONFIG_SIZE_WIDTH => 200,
                        ImageConfigDefinition::CONFIG_SIZE_HEIGHT => 100,
                        ImageConfigDefinition::CONFIG_SIZE_CROP => true,
                        ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                        ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZES => [],
                    ],
                ],
                ImageConfigDefinition::CONFIG_TYPES => [],
            ],
        ];

        $preparedConfig = $this->imageConfigLoader->loadFromArray($inputConfig);

        $imageEntityConfig = $preparedConfig[$inputConfig[0][ImageConfigDefinition::CONFIG_CLASS]];
        $this->assertCount(1, $imageEntityConfig->getSizeConfigs());

        $imageSize = $imageEntityConfig->getSizeConfigByType(null, ImageConfig::ORIGINAL_SIZE_NAME);

        $this->assertInstanceOf(ImageSizeConfig::class, $imageSize);
        $this->assertSame(100, $imageSize->getHeight());
        $this->assertSame(200, $imageSize->getWidth());
        $this->assertTrue($imageSize->getCrop());
    }
}
