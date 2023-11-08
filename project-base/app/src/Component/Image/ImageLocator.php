<?php

declare(strict_types=1);

namespace App\Component\Image;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator as BaseImageLocator;

/**
 * @method string getRelativeImageFilepath(\App\Component\Image\Image $image)
 * @method string getAbsoluteImageFilepath(\App\Component\Image\Image $image)
 * @method bool imageExists(\App\Component\Image\Image $image)
 */
class ImageLocator extends BaseImageLocator
{
    /**
     * @param string $imageDir
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct($imageDir, ImageConfig $imageConfig, FilesystemOperator $filesystem)
    {
        parent::__construct($imageDir, $imageConfig, $filesystem);
    }

    /**
     * @param \App\Component\Image\Image $image
     * @param string|null $friendlyUrlSlug
     * @return string
     */
    public function getRelativeImageFilepathWithSlug(Image $image, ?string $friendlyUrlSlug): string
    {
        $path = $this->getRelativeImagePath($image->getEntityName(), $image->getType());

        return $path . $image->getSeoFilename($friendlyUrlSlug);
    }
}
