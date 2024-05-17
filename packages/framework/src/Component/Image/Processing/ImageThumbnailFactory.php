<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Processing;

use Intervention\Image\Image;

class ImageThumbnailFactory
{
    protected const int THUMBNAIL_WIDTH = 140;
    protected const int THUMBNAIL_HEIGHT = 200;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor $imageProcessor
     */
    public function __construct(protected readonly ImageProcessor $imageProcessor)
    {
    }

    /**
     * @param string $filepath
     * @return \Intervention\Image\Image
     */
    public function getImageThumbnail(string $filepath): Image
    {
        $image = $this->imageProcessor->createInterventionImage($filepath);
        $this->imageProcessor->resize($image, static::THUMBNAIL_WIDTH, static::THUMBNAIL_HEIGHT);

        return $image;
    }
}
