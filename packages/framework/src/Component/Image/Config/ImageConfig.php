<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;

class ImageConfig
{
    /**
     * @var array<class-string, \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig>
     */
    protected array $imageEntityConfigsByClass;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[] $imageEntityConfigsByClass
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

    public function getEntityName(object $entity): string
    {
        return $this->getImageEntityConfig($entity)->getEntityName();
    }

    public function assertImageConfigByEntityNameExists(string $entityName, ?string $type): void
    {
        $entityConfig = $this->getEntityConfigByEntityName($entityName);

        if ($type !== null && !in_array($type, $entityConfig->getTypes(), true)) {
            throw new ImageTypeNotFoundException($entityName, $type);
        }
    }

    public function getImageEntityConfig(object $entity): ImageEntityConfig
    {
        foreach ($this->imageEntityConfigsByClass as $className => $entityConfig) {
            if ($entity instanceof $className) {
                return $entityConfig;
            }
        }

        throw new ImageEntityConfigNotFoundException(get_class($entity));
    }

    public function hasImageConfig(object $entity): bool
    {
        foreach (array_keys($this->imageEntityConfigsByClass) as $className) {
            if ($entity instanceof $className) {
                return true;
            }
        }

        return false;
    }

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

    public function getImageEntityConfigByClass(string $class): ImageEntityConfig
    {
        $normalizedClass = $this->entityNameResolver->resolve($class);

        if (array_key_exists($normalizedClass, $this->imageEntityConfigsByClass)) {
            return $this->imageEntityConfigsByClass[$normalizedClass];
        }

        throw new ImageEntityConfigNotFoundException($class);
    }
}
