<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config;

class ImageAdditionalSizeConfig
{
    /**
     * @param int|null $width
     * @param int|null $height
     * @param string $media
     */
    public function __construct(protected readonly ?int $width = null, protected readonly ?int $height = null, protected readonly string $media)
    {
    }

    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @return string
     */
    public function getMedia(): string
    {
        return $this->media;
    }
}
