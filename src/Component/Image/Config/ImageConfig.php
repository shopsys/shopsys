<?php

declare(strict_types=1);

namespace App\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig as BaseImageConfig;

/**
 * @method \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig getImageSizeConfigByImage(\App\Component\Image\Image $image, string|null $sizeName)
 */
class ImageConfig extends BaseImageConfig
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    private $entityNameResolver;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[] $imageEntityConfigsByClass
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        array $imageEntityConfigsByClass,
        EntityNameResolver $entityNameResolver
    ) {
        $this->entityNameResolver = $entityNameResolver;

        $imageEntityConfigsByNormalizedClass = [];
        foreach ($imageEntityConfigsByClass as $class => $imageEntityConfig) {
            $normalizedClass = $this->entityNameResolver->resolve($class);
            $imageEntityConfigsByNormalizedClass[$normalizedClass] = $imageEntityConfig;
        }

        parent::__construct($imageEntityConfigsByNormalizedClass);
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

        throw new \Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException($class);
    }
}
