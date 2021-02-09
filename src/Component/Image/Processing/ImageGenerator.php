<?php

declare(strict_types=1);

namespace App\Component\Image\Processing;

use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageGenerator as BaseImageGenerator;

/**
 * @method string generateAdditionalImageSizeAndGetFilepath(\App\Component\Image\Image $image, int $additionalIndex, string|null $sizeName)
 * @method checkSizeNameIsNotOriginal(\App\Component\Image\Image $image, string|null $sizeName)
 * @property \App\Component\Image\Config\ImageConfig $imageConfig
 * @property \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor $imageProcessor
 * @property \App\Component\Image\ImageLocator $imageLocator
 */
class ImageGenerator extends BaseImageGenerator
{
    /**
     * @deprecated Method should not be necessary when running on shopsys/framework v9.1.1. See https://github.com/shopsys/shopsys/pull/2232
     * @param \App\Component\Image\Image $image
     * @param string|null $sizeName
     * @return string
     */
    public function generateImageSizeAndGetFilepath(Image $image, $sizeName): string
    {
        $this->checkSizeNameIsNotOriginal($image, $sizeName);

        $sourceImageFilepath = $this->imageLocator->getAbsoluteImageFilepath($image, ImageConfig::ORIGINAL_SIZE_NAME);
        $targetImageFilepath = $this->imageLocator->getAbsoluteImageFilepath($image, $sizeName);
        $sizeConfig = $this->imageConfig->getImageSizeConfigByImage($image, $sizeName);

        $interventionImage = $this->imageProcessor->createInterventionImage($sourceImageFilepath);
        $this->imageProcessor->resizeBySizeConfig($interventionImage, $sizeConfig);

        $interventionImage->encode();

        $this->filesystem->put($targetImageFilepath, $interventionImage->getEncoded());

        return $targetImageFilepath;
    }
}
