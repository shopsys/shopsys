<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageSizeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Utils\Utils;

class ImageEntityConfig
{
    public const WITHOUT_NAME_KEY = '__NULL__';

    protected string $entityName;

    protected string $entityClass;

    /**
     * @param string $entityName
     * @param string $entityClass
     * @param mixed[] $sizeConfigsByType
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizeConfigs
     * @param mixed[] $multipleByType
     */
    public function __construct(
        string $entityName,
        string $entityClass,
        protected readonly array $sizeConfigsByType,
        protected readonly array $sizeConfigs,
        protected readonly array $multipleByType,
    ) {
        $this->entityName = $entityName;
        $this->entityClass = $entityClass;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return int[]|string[]
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
     * @return array<string, array<string, \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig>>
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
        }

        throw new ImageTypeNotFoundException($this->entityClass, $type);
    }

    /**
     * @param string|null $sizeName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
     */
    public function getSizeConfig($sizeName): \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
    {
        return $this->getSizeConfigFromSizeConfigs($this->sizeConfigs, $sizeName);
    }

    /**
     * @param string|null $type
     * @param string|null $sizeName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
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
     * @return bool
     */
    public function isMultiple($type): bool
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
    protected function getSizeConfigFromSizeConfigs($sizes, $sizeName): \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
    {
        $key = Utils::ifNull($sizeName, self::WITHOUT_NAME_KEY);

        if (array_key_exists($key, $sizes)) {
            return $sizes[$key];
        }

        throw new ImageSizeNotFoundException($this->entityClass, $sizeName);
    }
}
