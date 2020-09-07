<?php

declare(strict_types=1);

namespace App\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader as BaseImageConfigLoader;
use Symfony\Component\Filesystem\Filesystem;

class ImageConfigLoader extends BaseImageConfigLoader
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        Filesystem $filesystem,
        EntityNameResolver $entityNameResolver
    ) {
        parent::__construct($filesystem);
        $this->entityNameResolver = $entityNameResolver;
    }

    /**
     * @param string $filename
     * @return \App\Component\Image\Config\ImageConfig
     */
    public function loadFromYaml($filename)
    {
        $baseImageConfig = parent::loadFromYaml($filename);

        return new ImageConfig($baseImageConfig->getAllImageEntityConfigsByClass(), $this->entityNameResolver);
    }
}
