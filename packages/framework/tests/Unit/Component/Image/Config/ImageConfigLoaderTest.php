<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image\Config;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateEntityNameExceptionInvalid;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateTypeNameExceptionInvalid;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityChildWithFolder;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityChildWithNothing;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityChildWithTypes;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityDuplicateNameA;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityDuplicateNameAChild;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityDuplicateNameB;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityParentWithTypesAndFolder;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityWithDuplicateTypes;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityWithImage;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityWithMultipleImages;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityWithoutAttribute;
use Tests\FrameworkBundle\Unit\Component\Image\Config\Resources\TestEntityWithTypes;

class ImageConfigLoaderTest extends TestCase
{
    private ImageConfigLoader $imageConfigLoader;

    #[Override]
    protected function setUp(): void
    {
        $entityNameResolver = new EntityNameResolver([]);
        $this->imageConfigLoader = new ImageConfigLoader($entityNameResolver);
    }

    public function testLoadFromEntityClassesThrowsOnDuplicateTypeName(): void
    {
        $this->expectException(DuplicateTypeNameExceptionInvalid::class);

        $this->imageConfigLoader->loadFromEntityClasses([
            TestEntityWithDuplicateTypes::class,
        ]);
    }

    public function testLoadFromEntityClassesBasic(): void
    {
        $config = $this->imageConfigLoader->loadFromEntityClasses([
            TestEntityWithImage::class,
        ]);

        $entityConfig = $config->getImageEntityConfigByClass(TestEntityWithImage::class);

        $this->assertSame('testEntity', $entityConfig->getEntityName());
        $this->assertFalse($entityConfig->isMultiple(null));
        $this->assertSame(['default'], $entityConfig->getTypes());
    }

    public function testLoadFromEntityClassesMultiple(): void
    {
        $config = $this->imageConfigLoader->loadFromEntityClasses([
            TestEntityWithMultipleImages::class,
        ]);

        $entityConfig = $config->getImageEntityConfigByClass(TestEntityWithMultipleImages::class);

        $this->assertSame('testEntityMultiple', $entityConfig->getEntityName());
        $this->assertTrue($entityConfig->isMultiple(null));
        $this->assertSame(['default'], $entityConfig->getTypes());
    }

    public function testLoadFromEntityClassesWithTypes(): void
    {
        $config = $this->imageConfigLoader->loadFromEntityClasses([
            TestEntityWithTypes::class,
        ]);

        $entityConfig = $config->getImageEntityConfigByClass(TestEntityWithTypes::class);

        $this->assertSame('testEntityWithTypes', $entityConfig->getEntityName());
        $this->assertSame(['web', 'mobile'], $entityConfig->getTypes());
        $this->assertFalse($entityConfig->isMultiple('web'));
        $this->assertTrue($entityConfig->isMultiple('mobile'));
    }

    public function testLoadFromEntityClassesSkipsClassesWithoutAttribute(): void
    {
        $config = $this->imageConfigLoader->loadFromEntityClasses([
            TestEntityWithoutAttribute::class,
            TestEntityWithImage::class,
        ]);

        $this->assertFalse($config->hasImageConfig(new TestEntityWithoutAttribute()));
        $this->assertTrue($config->hasImageConfig(new TestEntityWithImage()));
    }

    public function testLoadFromEntityClassesWithDuplicateFolderNameThrowsForUnrelatedEntities(): void
    {
        $this->expectException(DuplicateEntityNameExceptionInvalid::class);

        $this->imageConfigLoader->loadFromEntityClasses([
            TestEntityDuplicateNameA::class,
            TestEntityDuplicateNameB::class,
        ]);
    }

    public function testLoadFromEntityClassesWithDuplicateFolderNameAllowsChildEntity(): void
    {
        $config = $this->imageConfigLoader->loadFromEntityClasses([
            TestEntityDuplicateNameA::class,
            TestEntityDuplicateNameAChild::class,
        ]);

        $this->assertFalse($config->hasImageConfig(new TestEntityDuplicateNameA()));
        $this->assertTrue($config->hasImageConfig(new TestEntityDuplicateNameAChild()));

        $entityConfig = $config->getImageEntityConfigByClass(TestEntityDuplicateNameAChild::class);
        $this->assertSame('duplicateName', $entityConfig->getEntityName());
    }

    public function testChildInheritsTypesAndFolderFromParent(): void
    {
        $config = $this->imageConfigLoader->loadFromEntityClasses([
            TestEntityParentWithTypesAndFolder::class,
            TestEntityChildWithNothing::class,
        ]);

        $entityConfig = $config->getImageEntityConfigByClass(TestEntityChildWithNothing::class);

        $this->assertSame('parentFolder', $entityConfig->getEntityName());
        $this->assertSame(['web', 'mobile'], $entityConfig->getTypes());
        $this->assertFalse($entityConfig->isMultiple('web'));
        $this->assertTrue($entityConfig->isMultiple('mobile'));
    }

    public function testChildOverridesTypesButInheritsFolderFromParent(): void
    {
        $config = $this->imageConfigLoader->loadFromEntityClasses([
            TestEntityParentWithTypesAndFolder::class,
            TestEntityChildWithTypes::class,
        ]);

        $entityConfig = $config->getImageEntityConfigByClass(TestEntityChildWithTypes::class);

        $this->assertSame('parentFolder', $entityConfig->getEntityName());
        $this->assertSame(['thumbnail', 'banner'], $entityConfig->getTypes());
        $this->assertFalse($entityConfig->isMultiple('thumbnail'));
        $this->assertTrue($entityConfig->isMultiple('banner'));
    }

    public function testChildOverridesFolderButInheritsTypesFromParent(): void
    {
        $config = $this->imageConfigLoader->loadFromEntityClasses([
            TestEntityParentWithTypesAndFolder::class,
            TestEntityChildWithFolder::class,
        ]);

        $entityConfig = $config->getImageEntityConfigByClass(TestEntityChildWithFolder::class);

        $this->assertSame('childFolder', $entityConfig->getEntityName());
        $this->assertSame(['web', 'mobile'], $entityConfig->getTypes());
        $this->assertFalse($entityConfig->isMultiple('web'));
        $this->assertTrue($entityConfig->isMultiple('mobile'));
    }

    public function testLoadFromEntityClassesWithDuplicateFolderNameAllowsChildEntityRegardlessOfOrder(): void
    {
        $config = $this->imageConfigLoader->loadFromEntityClasses([
            TestEntityDuplicateNameAChild::class,
            TestEntityDuplicateNameA::class,
        ]);

        $this->assertFalse($config->hasImageConfig(new TestEntityDuplicateNameA()));
        $this->assertTrue($config->hasImageConfig(new TestEntityDuplicateNameAChild()));

        $entityConfig = $config->getImageEntityConfigByClass(TestEntityDuplicateNameAChild::class);
        $this->assertSame('duplicateName', $entityConfig->getEntityName());
    }
}
