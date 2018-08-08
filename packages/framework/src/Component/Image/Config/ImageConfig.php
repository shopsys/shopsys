<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\Image\Image;

class ImageConfig
{
    const ORIGINAL_SIZE_NAME = 'original';
    const DEFAULT_SIZE_NAME = 'default';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[]
     */
    private $imageEntityConfigsByClass;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[] $imageEntityConfigsByClass
     */
    public function __construct(array $imageEntityConfigsByClass)
    {
        $this->imageEntityConfigsByClass = $imageEntityConfigsByClass;
    }

    /**
     * @param Object $entity
     */
    public function getEntityName($entity): string
    {
        $entityConfig = $this->getImageEntityConfig($entity);
        return $entityConfig->getEntityName();
    }

    /**
     * @param Object $entity
     * @param string|null $type
     * @param string|null $sizeName
     */
    public function getImageSizeConfigByEntity($entity, $type, $sizeName): \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
    {
        $entityConfig = $this->getImageEntityConfig($entity);
        return $entityConfig->getSizeConfigByType($type, $sizeName);
    }

    /**
     * @param string $entityName
     * @param string|null $type
     * @param string|null $sizeName
     */
    public function getImageSizeConfigByEntityName($entityName, $type, $sizeName): \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
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
     * @param string|null $sizeName
     */
    public function getImageSizeConfigByImage(Image $image, $sizeName): \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
    {
        $entityConfig = $this->getEntityConfigByEntityName($image->getEntityName());
        return $entityConfig->getSizeConfigByType($image->getType(), $sizeName);
    }

    /**
     * @param Object $entity
     */
    public function getImageEntityConfig($entity): \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig
    {
        foreach ($this->imageEntityConfigsByClass as $className => $entityConfig) {
            if ($entity instanceof $className) {
                return $entityConfig;
            }
        }

        throw new \Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException(
            $entity ? get_class($entity) : null
        );
    }

    /**
     * @param object $entity
     */
    public function hasImageConfig($entity): bool
    {
        foreach ($this->imageEntityConfigsByClass as $className => $entityConfig) {
            if ($entity instanceof $className) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $entityName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
     */
    public function getEntityConfigByEntityName($entityName)
    {
        foreach ($this->imageEntityConfigsByClass as $entityConfig) {
            if ($entityConfig->getEntityName() === $entityName) {
                return $entityConfig;
            }
        }

        throw new \Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException($entityName);
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
     */
    public function getImageEntityConfigByClass($class): \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig
    {
        if (array_key_exists($class, $this->imageEntityConfigsByClass)) {
            return $this->imageEntityConfigsByClass[$class];
        }

        throw new \Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException($class);
    }
}
