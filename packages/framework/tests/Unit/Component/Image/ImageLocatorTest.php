<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image;

use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigDefinition;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;

class ImageLocatorTest extends TestCase
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
            ],
        ];

        $filesystem = new Filesystem();
        $entityNameResolver = new EntityNameResolver([]);
        $imageConfigLoader = new ImageConfigLoader($filesystem, $entityNameResolver);
        $imageEntityConfigByClass = $imageConfigLoader->loadFromArray($inputConfig);

        return new ImageConfig($imageEntityConfigByClass, $entityNameResolver);
    }

    /**
     * @return array
     */
    public function getRelativeImagePathProvider(): array
    {
        return [
            [
                'Name_1',
                'TypeName_1',
                'Name_1/TypeName_1/',
            ],
            [
                'Name_1',
                null,
                'Name_1/',
            ],
        ];
    }

    /**
     * @dataProvider getRelativeImagePathProvider
     * @param string $entityName
     * @param string|null $type
     * @param string $expectedPath
     */
    public function testGetRelativeImagePath(string $entityName, ?string $type, string $expectedPath): void
    {
        $filesystemMock = $this->createMock(FilesystemOperator::class);
        $imageLocator = new ImageLocator('imageDir', $this->getBaseImageConfig(), $filesystemMock);

        $this->assertSame($expectedPath, $imageLocator->getRelativeImagePath($entityName, $type));
    }

    /**
     * @return array
     */
    public function getRelativeImagePathExceptionProvider(): array
    {
        return [
            [
                'NonexistentName',
                null,
                ImageEntityConfigNotFoundException::class,
            ],
            [
                'Name_1',
                'NonexistentTypeName',
                ImageTypeNotFoundException::class,
            ],
        ];
    }

    /**
     * @dataProvider getRelativeImagePathExceptionProvider
     * @param string $entityName
     * @param string|null $type
     * @param string $exceptionClass
     */
    public function testGetRelativeImagePathException(string $entityName, ?string $type, string $exceptionClass): void
    {
        $filesystemMock = $this->createMock(FilesystemOperator::class);
        $imageLocator = new ImageLocator('imageDir', $this->getBaseImageConfig(), $filesystemMock);

        $this->expectException($exceptionClass);
        $imageLocator->getRelativeImagePath($entityName, $type);
    }
}
