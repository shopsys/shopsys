<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use ReflectionAttribute;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImageFolder;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateEntityNameExceptionInvalid;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateTypeNameExceptionInvalid;

class ImageConfigLoader
{
    /**
     * @var array<class-string,\Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig>
     */
    protected array $foundEntityConfigs;

    /**
     * @var string[]
     */
    protected array $foundFolderNames;

    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param class-string[] $entityClasses
     */
    public function loadFromEntityClasses(array $entityClasses): ImageConfig
    {
        $this->foundEntityConfigs = [];
        $this->foundFolderNames = [];

        foreach ($entityClasses as $entityClass) {
            $imageAttributes = $this->getImageAttributesFromClass($entityClass);

            if ($imageAttributes === []) {
                continue;
            }

            $folderName = $this->getFolderName($entityClass);

            $this->processEntity($entityClass, $folderName, $imageAttributes);
        }

        return new ImageConfig($this->foundEntityConfigs, $this->entityNameResolver);
    }

    /**
     * @param class-string $entityClass
     * @return array<\Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage>
     */
    protected function getImageAttributesFromClass(string $entityClass): array
    {
        $reflectionClass = new ReflectionClass($entityClass);

        do {
            $attributes = $reflectionClass->getAttributes(EntityImage::class, ReflectionAttribute::IS_INSTANCEOF);

            if ($attributes !== []) {
                return array_map(static fn (ReflectionAttribute $attribute) => $attribute->newInstance(), $attributes);
            }
        } while ($reflectionClass = $reflectionClass->getParentClass());

        return [];
    }

    protected function getFolderName(string $entityClass): string
    {
        $originalReflectionClass = new ReflectionClass($entityClass);
        $reflectionClass = $originalReflectionClass;

        do {
            $attributes = $reflectionClass->getAttributes(EntityImageFolder::class, ReflectionAttribute::IS_INSTANCEOF);

            if ($attributes !== []) {
                return $attributes[0]->newInstance()->name;
            }
        } while ($reflectionClass = $reflectionClass->getParentClass());

        return lcfirst($originalReflectionClass->getShortName());
    }

    protected function processEntity(string $entityClass, string $folderName, array $imageAttributes): void
    {
        if (array_key_exists($entityClass, $this->foundEntityConfigs)) {
            throw new DuplicateEntityNameExceptionInvalid($folderName);
        }

        $types = $this->prepareTypes($imageAttributes);
        $multipleByType = $this->getMultipleByType($imageAttributes);

        $imageEntityConfig = new ImageEntityConfig($folderName, $entityClass, $types, $multipleByType);

        if (isset($this->foundFolderNames[$folderName])) {
            $existingClass = $this->foundFolderNames[$folderName];

            if (!is_subclass_of($entityClass, $existingClass) && !is_subclass_of($existingClass, $entityClass)) {
                throw new DuplicateEntityNameExceptionInvalid($folderName);
            }

            if (is_subclass_of($existingClass, $entityClass)) {
                return;
            }

            unset($this->foundEntityConfigs[$this->foundFolderNames[$folderName]]);
        }

        $this->foundEntityConfigs[$entityClass] = $imageEntityConfig;
        $this->foundFolderNames[$folderName] = $entityClass;
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage> $imageAttributes
     * @return string[]
     */
    protected function prepareTypes(array $imageAttributes): array
    {
        $result = [];

        foreach ($imageAttributes as $imageAttribute) {
            if (in_array($imageAttribute->name, $result, true)) {
                throw new DuplicateTypeNameExceptionInvalid($imageAttribute->name);
            }

            $result[] = $imageAttribute->name;
        }

        return $result;
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage> $imageAttributes
     * @return array<string,bool>
     */
    protected function getMultipleByType(array $imageAttributes): array
    {
        $multipleByType = [];

        foreach ($imageAttributes as $imageAttribute) {
            $key = $imageAttribute->name === EntityImage::DEFAULT_NAME ? ImageEntityConfig::WITHOUT_NAME_KEY : $imageAttribute->name;

            $multipleByType[$key] = $imageAttribute->multiple;
        }

        return $multipleByType;
    }
}
