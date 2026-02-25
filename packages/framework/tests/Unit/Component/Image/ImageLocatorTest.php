<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image;

use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityForImageLocator;

class ImageLocatorTest extends TestCase
{
    private function getBaseImageConfig(): ImageConfig
    {
        $entityNameResolver = new EntityNameResolver([]);
        $imageConfigLoader = new ImageConfigLoader($entityNameResolver);

        return $imageConfigLoader->loadFromEntityClasses([
            TestEntityForImageLocator::class,
        ]);
    }

    public static function getRelativeImagePathProvider(): array
    {
        return [
            [
                'Name_1',
                'TypeName_1',
                'Name_1/TypeName_1',
            ],
            [
                'Name_1',
                null,
                'Name_1',
            ],
        ];
    }

    #[DataProvider('getRelativeImagePathProvider')]
    public function testGetRelativeImagePath(string $entityName, ?string $type, string $expectedPath): void
    {
        $filesystemStub = $this->createStub(FilesystemOperator::class);
        $imageLocator = new ImageLocator('imageDir', $this->getBaseImageConfig(), $filesystemStub);

        $this->assertSame($expectedPath, $imageLocator->getRelativeImagePath($entityName, $type));
    }

    public static function getRelativeImagePathExceptionProvider(): array
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

    #[DataProvider('getRelativeImagePathExceptionProvider')]
    public function testGetRelativeImagePathException(string $entityName, ?string $type, string $exceptionClass): void
    {
        $filesystemStub = $this->createStub(FilesystemOperator::class);
        $imageLocator = new ImageLocator('imageDir', $this->getBaseImageConfig(), $filesystemStub);

        $this->expectException($exceptionClass);

        $imageLocator->getRelativeImagePath($entityName, $type);
    }
}
