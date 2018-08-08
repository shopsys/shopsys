<?php

namespace Shopsys\FrameworkBundle\Component\Image\Processing;

class ImageThumbnailFactory
{
    const THUMBNAIL_WIDTH = 140;
    const THUMBNAIL_HEIGHT = 200;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessingService
     */
    private $imageProcessingService;

    public function __construct(ImageProcessingService $imageProcessingService)
    {
        $this->imageProcessingService = $imageProcessingService;
    }
    
    public function getImageThumbnail(string $filepath): \Intervention\Image\Image
    {
        $image = $this->imageProcessingService->createInterventionImage($filepath);
        $this->imageProcessingService->resize($image, self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);

        return $image;
    }
}
