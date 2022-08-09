<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageSizeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Utils\Utils;

class ImageEntityConfig
{
    public const WITHOUT_NAME_KEY = '__NULL__';

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var class-string
     */
    protected $entityClass;

    /**
     * @var array<string, \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]>
     */
    protected $sizeConfigsByType;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    protected $sizeConfigs;

    /**
     * @var array<string, bool>
     */
    protected $multipleByType;

    /**
     * @param string $entityName
     * @param class-string $entityClass
     * @param array<string, \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]> $sizeConfigsByType
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizeConfigs
     * @param array<string, bool> $multipleByType
     */
    public function __construct(string $entityName, string $entityClass, array $sizeConfigsByType, array $sizeConfigs, array $multipleByType)
    {
        $this->entityName = $entityName;
        $this->entityClass = $entityClass;
        $this->sizeConfigsByType = $sizeConfigsByType;
        $this->sizeConfigs = $sizeConfigs;
        $this->multipleByType = $multipleByType;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return string[]
     */
    public function getTypes(): array
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
     * @return array<string, \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]>
     */
    public function getSizeConfigsByTypes(): array
    {
        return $this->sizeConfigsByType;
    }

    /**
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    public function getSizeConfigsByType(string $type): array
    {
        if (array_key_exists($type, $this->sizeConfigsByType)) {
            return $this->sizeConfigsByType[$type];
        }
        throw new ImageTypeNotFoundException($this->entityClass, $type);
    }

    /**
     * @param string|null $sizeName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
     */
    public function getSizeConfig(?string $sizeName): ImageSizeConfig
    {
        return $this->getSizeConfigFromSizeConfigs($this->sizeConfigs, $sizeName);
    }

    /**
     * @param string|null $type
     * @param string|null $sizeName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
     */
    public function getSizeConfigByType(?string $type, ?string $sizeName): ImageSizeConfig
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
     * @return bool
     */
    public function isMultiple(?string $type): bool
    {
        $key = Utils::ifNull($type, self::WITHOUT_NAME_KEY);
        if (array_key_exists($key, $this->multipleByType)) {
            return $this->multipleByType[$key];
        }
        throw new ImageTypeNotFoundException($this->entityClass, $type);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizes
     * @param string $sizeName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
     */
    protected function getSizeConfigFromSizeConfigs(array $sizes, string $sizeName): ImageSizeConfig
    {
        $key = Utils::ifNull($sizeName, self::WITHOUT_NAME_KEY);
        if (array_key_exists($key, $sizes)) {
            return $sizes[$key];
        }
        throw new ImageSizeNotFoundException($this->entityClass, $sizeName);
    }
}
