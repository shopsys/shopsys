<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\Utils\Utils;

class ImageEntityConfig
{
    const WITHOUT_NAME_KEY = '__NULL__';

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var array
     */
    private $sizeConfigsByType;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    private $sizeConfigs;

    /**
     * @var array
     */
    private $multipleByType;

    /**
     * @param string $entityName
     * @param string $entityClass
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizeConfigs
     */
    public function __construct($entityName, $entityClass, array $sizeConfigsByType, array $sizeConfigs, array $multipleByType)
    {
        $this->entityName = $entityName;
        $this->entityClass = $entityClass;
        $this->sizeConfigsByType = $sizeConfigsByType;
        $this->sizeConfigs = $sizeConfigs;
        $this->multipleByType = $multipleByType;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
    
    public function getTypes()
    {
        return array_keys($this->sizeConfigsByType);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    public function getSizeConfigs(): array
    {
        return $this->sizeConfigs;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    public function getSizeConfigsByTypes(): array
    {
        return $this->sizeConfigsByType;
    }

    /**
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    public function getSizeConfigsByType($type): array
    {
        if (array_key_exists($type, $this->sizeConfigsByType)) {
            return $this->sizeConfigsByType[$type];
        } else {
            throw new \Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException($this->entityClass, $type);
        }
    }

    /**
     * @param string|null $sizeName
     */
    public function getSizeConfig($sizeName): \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
    {
        return $this->getSizeConfigFromSizeConfigs($this->sizeConfigs, $sizeName);
    }

    /**
     * @param string|null $type
     * @param string|null $sizeName
     */
    public function getSizeConfigByType($type, $sizeName): \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
    {
        if ($type === null) {
            $typeSizes = $this->sizeConfigs;
        } else {
            $typeSizes = $this->getSizeConfigsByType($type);
        }
        return $this->getSizeConfigFromSizeConfigs($typeSizes, $sizeName);
    }

    /**
     * @param string|null $type
     */
    public function isMultiple($type): bool
    {
        $key = Utils::ifNull($type, self::WITHOUT_NAME_KEY);
        if (array_key_exists($key, $this->multipleByType)) {
            return $this->multipleByType[$key];
        } else {
            throw new \Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException($this->entityClass, $type);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizes
     * @param string $sizeName
     */
    private function getSizeConfigFromSizeConfigs($sizes, $sizeName): \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
    {
        $key = Utils::ifNull($sizeName, self::WITHOUT_NAME_KEY);
        if (array_key_exists($key, $sizes)) {
            return $sizes[$key];
        } else {
            throw new \Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageSizeNotFoundException($this->entityClass, $sizeName);
        }
    }
}
