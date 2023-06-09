<?php

namespace Shopsys\FrameworkBundle\Component\Image\Processing;

class ImageThumbnailFactory
{
    protected const THUMBNAIL_WIDTH = 140;
    protected const THUMBNAIL_HEIGHT = 200;

    protected ImageProcessor $imageProcessor;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor $imageProcessor
     */
    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @param string $filepath
     * @return \Intervention\Image\Image
     */
    public function getImageThumbnail($filepath)
    {
        $image = $this->imageProcessor->createInterventionImage($filepath);
        $this->imageProcessor->resize($image, static::THUMBNAIL_WIDTH, static::THUMBNAIL_HEIGHT);

        return $image;
    }
}
