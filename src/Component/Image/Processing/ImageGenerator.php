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
use Symfony\Bridge\Monolog\Logger;

/**
 * @method string generateAdditionalImageSizeAndGetFilepath(\App\Component\Image\Image $image, int $additionalIndex, string|null $sizeName)
 * @method checkSizeNameIsNotOriginal(\App\Component\Image\Image $image, string|null $sizeName)
 * @property \App\Component\Image\Config\ImageConfig $imageConfig
 * @property \App\Component\Image\Processing\ImageProcessor $imageProcessor
 * @property \App\Component\Image\ImageLocator $imageLocator
 */
class ImageGenerator extends BaseImageGenerator
{
    /**
     * @var \App\Component\Image\Kraken\Processing\ImageKrakenProcessor
     */
    private $imageKrakenProcessor;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @param \App\Component\Image\Processing\ImageProcessor $imageProcessor
     * @param \App\Component\Image\ImageLocator $imageLocator
     * @param \App\Component\Image\Config\ImageConfig $imageConfig
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \App\Component\Image\Kraken\Processing\ImageKrakenProcessor $imageKrakenProcessor
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function __construct(
        ImageProcessor $imageProcessor,
        ImageLocator $imageLocator,
        ImageConfig $imageConfig,
        FilesystemInterface $filesystem,
        ImageKrakenProcessor $imageKrakenProcessor,
        Logger $logger
    ) {
        parent::__construct($imageProcessor, $imageLocator, $imageConfig, $filesystem);
        $this->imageKrakenProcessor = $imageKrakenProcessor;
        $this->logger = $logger;
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
            $this->processImageInKraken($sourceImageFilepath, $targetImageFilepath, $sizeConfig, $image);
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

        $this->filesystem->put($targetImageFilepath, $interventionImage->getEncoded());
    }

    /**
     * @param string $sourceImageFilepath
     * @param string $targetImageFilepath
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig $sizeConfig
     * @param \App\Component\Image\Image $image
     */
    private function processImageInKraken(string $sourceImageFilepath, string $targetImageFilepath, ImageSizeConfig $sizeConfig, Image $image): void
    {
        $krakenImageData = $this->imageKrakenProcessor->resizeBySizeConfig($sourceImageFilepath, [$sizeConfig]);

        if (!array_key_exists('success', $krakenImageData) || $krakenImageData['success'] === false) {
            $this->logger->addError(
                sprintf(
                    'Generating image by kraken error: image id: %s with result: %s',
                    $image->getId(),
                    $krakenImageData['message'] ?? implode('|', $krakenImageData)
                )
            );
            $this->processImageInFramework($sourceImageFilepath, $targetImageFilepath, $sizeConfig);
            return;
        }

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
