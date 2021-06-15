<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;

class ImageConfig
{
    use SetterInjectionTrait;

    public const ORIGINAL_SIZE_NAME = 'original';
    public const DEFAULT_SIZE_NAME = 'default';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[]
     */
    protected $imageEntityConfigsByClass;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[] $imageEntityConfigsByClass
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(array $imageEntityConfigsByClass, ?EntityNameResolver $entityNameResolver = null)
    {
        $this->entityNameResolver = $entityNameResolver;
        if ($entityNameResolver !== null) {
            $this->setUpImageEntityConfigsByClass($imageEntityConfigsByClass);
        } else {
            $this->imageEntityConfigsByClass = $imageEntityConfigsByClass;
        }
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
     * @required
     * @internal This function will be replaced by constructor injection in next major
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function setEntityNameResolver(EntityNameResolver $entityNameResolver): void
    {
        $this->setDependency($entityNameResolver, 'entityNameResolver');
        $this->setUpImageEntityConfigsByClass($this->imageEntityConfigsByClass);
    }

    /**
     * @param object $entity
     * @return string
     */
    public function getEntityName($entity)
    {
        $entityConfig = $this->getImageEntityConfig($entity);
        return $entityConfig->getEntityName();
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @param string|null $sizeName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
     */
    public function getImageSizeConfigByEntity($entity, $type, $sizeName)
    {
        $entityConfig = $this->getImageEntityConfig($entity);
        return $entityConfig->getSizeConfigByType($type, $sizeName);
    }

    /**
     * @param string $entityName
     * @param string|null $type
     * @param string|null $sizeName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
     */
    public function getImageSizeConfigByEntityName($entityName, $type, $sizeName)
    {
        $entityConfig = $this->getEntityConfigByEntityName($entityName);
        return $entityConfig->getSizeConfigByType($type, $sizeName);
    }

    /**
     * @param string $entityName
     * @param string|null $type
     * @param string|null $sizeName
     */
    public function assertImageSizeConfigByEntityNameExists($entityName, $type, $sizeName)
    {
        $this->getImageSizeConfigByEntityName($entityName, $type, $sizeName);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     * @param string|null $sizeName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
     */
    public function getImageSizeConfigByImage(Image $image, $sizeName)
    {
        $entityConfig = $this->getEntityConfigByEntityName($image->getEntityName());
        return $entityConfig->getSizeConfigByType($image->getType(), $sizeName);
    }

    /**
     * @param object|null $entity
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig
     */
    public function getImageEntityConfig($entity)
    {
        foreach ($this->imageEntityConfigsByClass as $className => $entityConfig) {
            if ($entity instanceof $className) {
                return $entityConfig;
            }
        }

        throw new ImageEntityConfigNotFoundException(
            $entity ? get_class($entity) : null
        );
    }

    /**
     * @param object $entity
     * @return bool
     */
    public function hasImageConfig($entity)
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
    public function getEntityConfigByEntityName($entityName)
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
    public function getAllImageEntityConfigsByClass()
    {
        return $this->imageEntityConfigsByClass;
    }

    /**
     * @param string $class
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig
     */
    public function getImageEntityConfigByClass($class)
    {
        $normalizedClass = $this->entityNameResolver->resolve($class);
        if (array_key_exists($normalizedClass, $this->imageEntityConfigsByClass)) {
            return $this->imageEntityConfigsByClass[$normalizedClass];
        }

        throw new ImageEntityConfigNotFoundException($class);
    }
}
