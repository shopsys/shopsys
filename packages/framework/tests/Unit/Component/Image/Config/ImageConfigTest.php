<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image\Config;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityWithImage;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityWithTypes;

class ImageConfigTest extends TestCase
{
    private function getBaseImageConfig(): ImageConfig
    {
        $entityNameResolver = new EntityNameResolver([]);
        $imageConfigLoader = new ImageConfigLoader($entityNameResolver);

        return $imageConfigLoader->loadFromEntityClasses([
            TestEntityWithImage::class,
            TestEntityWithTypes::class,
        ]);
    }

    public function testGetEntityName(): void
    {
        $imageConfig = $this->getBaseImageConfig();
        $entity = new TestEntityWithImage();

        $this->assertSame('testEntity', $imageConfig->getEntityName($entity));
    }

    public function testGetEntityNameNotFound(): void
    {
        $imageConfig = $this->getBaseImageConfig();

        $this->expectException(ImageEntityConfigNotFoundException::class);

        $imageConfig->getEntityName($this);
    }

    public function testGetImageEntityConfig(): void
    {
        $imageConfig = $this->getBaseImageConfig();
        $entity = new TestEntityWithImage();

        $imageEntityConfig = $imageConfig->getImageEntityConfig($entity);

        $this->assertSame('testEntity', $imageEntityConfig->getEntityName());
    }

    public function testGetImageEntityConfigNotFound(): void
    {
        $imageConfig = $this->getBaseImageConfig();

        $this->expectException(ImageEntityConfigNotFoundException::class);

        $imageConfig->getImageEntityConfig($this);
    }
}
