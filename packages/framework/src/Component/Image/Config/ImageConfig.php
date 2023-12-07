<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;

class ImageConfig
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[]
     */
    protected array $imageEntityConfigsByClass;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[] $imageEntityConfigsByClass
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        array $imageEntityConfigsByClass,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
        $this->setUpImageEntityConfigsByClass($imageEntityConfigsByClass);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[] $imageEntityConfigsByClass
     */
    protected function setUpImageEntityConfigsByClass(array $imageEntityConfigsByClass): void
    {
        $imageEntityConfigsByNormalizedClass = [];

        foreach ($imageEntityConfigsByClass as $class => $imageEntityConfig) {
            $normalizedClass = $this->entityNameResolver->resolve($class);
            $imageEntityConfigsByNormalizedClass[$normalizedClass] = $imageEntityConfig;
        }

        $this->imageEntityConfigsByClass = $imageEntityConfigsByNormalizedClass;
    }

    /**
     * @param object $entity
     * @return string
     */
    public function getEntityName(object $entity): string
    {
        return $this->getImageEntityConfig($entity)->getEntityName();
    }

    /**
     * @param string $entityName
     * @param string|null $type
     */
    public function assertImageConfigByEntityNameExists(string $entityName, ?string $type): void
    {
        $entityConfig = $this->getEntityConfigByEntityName($entityName);

        if ($type !== null && !in_array($type, $entityConfig->getTypes(), true)) {
            throw new ImageTypeNotFoundException($entityName, $type);
        }
    }

    /**
     * @param object $entity
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig
     */
    public function getImageEntityConfig(object $entity): ImageEntityConfig
    {
        foreach ($this->imageEntityConfigsByClass as $className => $entityConfig) {
            if ($entity instanceof $className) {
                return $entityConfig;
            }
        }

        throw new ImageEntityConfigNotFoundException(get_class($entity));
    }

    /**
     * @param object $entity
     * @return bool
     */
    public function hasImageConfig(object $entity): bool
    {
        foreach (array_keys($this->imageEntityConfigsByClass) as $className) {
            if ($entity instanceof $className) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $entityName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig
     */
    protected function getEntityConfigByEntityName(string $entityName): ImageEntityConfig
    {
        foreach ($this->imageEntityConfigsByClass as $entityConfig) {
            if ($entityConfig->getEntityName() === $entityName) {
                return $entityConfig;
            }
        }

        throw new ImageEntityConfigNotFoundException($entityName);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[]
     */
    public function getAllImageEntityConfigsByClass(): array
    {
        return $this->imageEntityConfigsByClass;
    }

    /**
     * @param string $class
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig
     */
    public function getImageEntityConfigByClass(string $class): ImageEntityConfig
    {
        $normalizedClass = $this->entityNameResolver->resolve($class);

        if (array_key_exists($normalizedClass, $this->imageEntityConfigsByClass)) {
            return $this->imageEntityConfigsByClass[$normalizedClass];
        }

        throw new ImageEntityConfigNotFoundException($class);
    }
}
