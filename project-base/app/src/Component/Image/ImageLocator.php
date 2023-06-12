<?php

declare(strict_types=1);

namespace App\Component\Image;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator as BaseImageLocator;

/**
 * @method string getRelativeImageFilepath(\App\Component\Image\Image $image, string|null $sizeName)
 * @method string getAbsoluteImageFilepath(\App\Component\Image\Image $image, string|null $sizeName)
 * @method string getAbsoluteAdditionalImageFilepath(\App\Component\Image\Image $image, int $additionalIndex, string|null $sizeName)
 * @method bool imageExists(\App\Component\Image\Image $image)
 * @method string getRelativeAdditionalImageFilepath(\App\Component\Image\Image $image, int $additionalIndex, string|null $sizeName)
 */
class ImageLocator extends BaseImageLocator
{
    protected const ADDITIONAL_IMAGE_MASK = '{index}--{filename}';

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
     * @param string|null $sizeName
     * @param string|null $friendlyUrlSlug
     * @return string
     */
    public function getRelativeImageFilepathWithSlug(Image $image, ?string $sizeName, ?string $friendlyUrlSlug): string
    {
        $path = $this->getRelativeImagePath($image->getEntityName(), $image->getType(), $sizeName);

        return $path . $image->getSeoFilename($friendlyUrlSlug);
    }

    /**
     * @param \App\Component\Image\Image $image
     * @param int $additionalIndex
     * @param string|null $sizeName
     * @param string|null $friendlyUrlSlug
     * @return string
     */
    public function getRelativeAdditionalImageFilepathWithSlug(
        Image $image,
        int $additionalIndex,
        ?string $sizeName,
        ?string $friendlyUrlSlug,
    ) {
        $path = $this->getRelativeImagePath($image->getEntityName(), $image->getType(), $sizeName);

        $filename = $this->getAdditionalImageFilename($image->getSeoFilename($friendlyUrlSlug), $additionalIndex);

        return $path . $filename;
    }
}
