<?php

namespace Shopsys\FrameworkBundle\Component\Image\Processing;

use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;

class ImageGeneratorService
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessingService
     */
    private $imageProcessingService;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageLocator
     */
    private $imageLocator;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig
     */
    private $imageConfig;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    public function __construct(
        ImageProcessingService $imageProcessingService,
        ImageLocator $imageLocator,
        ImageConfig $imageConfig,
        FilesystemInterface $filesystem
    ) {
        $this->imageProcessingService = $imageProcessingService;
        $this->imageLocator = $imageLocator;
        $this->imageConfig = $imageConfig;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string|null $sizeName
     */
    public function generateImageSizeAndGetFilepath(Image $image, ?string $sizeName): string
    {
        if ($sizeName === ImageConfig::ORIGINAL_SIZE_NAME) {
            throw new \Shopsys\FrameworkBundle\Component\Image\Processing\Exception\OriginalSizeImageCannotBeGeneratedException(
                $image
            );
        }

        $sourceImageFilepath = $this->imageLocator->getAbsoluteImageFilepath($image, ImageConfig::ORIGINAL_SIZE_NAME);
        $targetImageFilepath = $this->imageLocator->getAbsoluteImageFilepath($image, $sizeName);
        $sizeConfig = $this->imageConfig->getImageSizeConfigByImage($image, $sizeName);

        $interventionImage = $this->imageProcessingService->createInterventionImage($sourceImageFilepath);
        $this->imageProcessingService->resizeBySizeConfig($interventionImage, $sizeConfig);

        $interventionImage->encode();

        $this->filesystem->put($targetImageFilepath, $interventionImage);

        return $targetImageFilepath;
    }
}
