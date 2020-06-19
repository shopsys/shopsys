<?php

declare(strict_types=1);

namespace App\Component\Image\Processing;

use App\Component\Image\Kraken\Processing\ImageKrakenProcessor;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageGenerator as BaseImageGenerator;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;

/**
 * @method string generateAdditionalImageSizeAndGetFilepath(\App\Component\Image\Image $image, int $additionalIndex, string|null $sizeName)
 * @method checkSizeNameIsNotOriginal(\App\Component\Image\Image $image, string|null $sizeName)
 * @property \App\Component\Image\Config\ImageConfig $imageConfig
 */
class ImageGenerator extends BaseImageGenerator
{
    /**
     * @var \App\Component\Image\Kraken\Processing\ImageKrakenProcessor
     */
    private $imageKrakenProcessor;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor $imageProcessor
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageLocator $imageLocator
     * @param \App\Component\Image\Config\ImageConfig $imageConfig
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \App\Component\Image\Kraken\Processing\ImageKrakenProcessor $imageKrakenProcessor
     */
    public function __construct(
        ImageProcessor $imageProcessor,
        ImageLocator $imageLocator,
        ImageConfig $imageConfig,
        FilesystemInterface $filesystem,
        ImageKrakenProcessor $imageKrakenProcessor
    ) {
        parent::__construct($imageProcessor, $imageLocator, $imageConfig, $filesystem);
        $this->imageKrakenProcessor = $imageKrakenProcessor;
    }

    /**
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

        if ($this->imageKrakenProcessor->isEnabled()) {
            $this->processImageInKraken($sourceImageFilepath, $targetImageFilepath, $sizeConfig);
        } else {
            $this->processImageInFramework($sourceImageFilepath, $targetImageFilepath, $sizeConfig);
        }

        return $targetImageFilepath;
    }

    /**
     * @param string $sourceImageFilepath
     * @param string $targetImageFilepath
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig $sizeConfig
     */
    private function processImageInFramework(string $sourceImageFilepath, string $targetImageFilepath, ImageSizeConfig $sizeConfig): void
    {
        $interventionImage = $this->imageProcessor->createInterventionImage($sourceImageFilepath);
        $this->imageProcessor->resizeBySizeConfig($interventionImage, $sizeConfig);

        $interventionImage->encode();

        $this->filesystem->put($targetImageFilepath, $interventionImage);
    }

    /**
     * @param string $sourceImageFilepath
     * @param string $targetImageFilepath
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig $sizeConfig
     */
    private function processImageInKraken(string $sourceImageFilepath, string $targetImageFilepath, ImageSizeConfig $sizeConfig): void
    {
        $krakenImageData = $this->imageKrakenProcessor->resizeBySizeConfig($sourceImageFilepath, [$sizeConfig]);
        $krakenImageDataResult = current($krakenImageData['results']);

        $file = file_get_contents($krakenImageDataResult['kraked_url']);

        $this->filesystem->put($targetImageFilepath, $file);
    }

    /**
     * @param \App\Component\Image\Image $image
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[] $sizesConfig
     * @return bool
     */
    public function processImageSizesInKraken(Image $image, array $sizesConfig): bool
    {
        $sourceImageFilepath = $this->imageLocator->getAbsoluteImageFilepath($image, ImageConfig::ORIGINAL_SIZE_NAME);

        $krakenImagesData = $this->imageKrakenProcessor->resizeBySizeConfig($sourceImageFilepath, $sizesConfig);

        if (!array_key_exists('results', $krakenImagesData)) {
            return false;
        }

        foreach ($krakenImagesData['results'] as $sizeName => $krakenImageData) {
            if ($sizeName === ImageConfig::DEFAULT_SIZE_NAME) {
                $sizeName = null;
            }
            $targetImageFilepath = $this->imageLocator->getAbsoluteImageFilepath($image, $sizeName);
            $file = file_get_contents($krakenImageData['kraked_url']);
            $this->filesystem->put($targetImageFilepath, $file);
        }

        return true;
    }
}
