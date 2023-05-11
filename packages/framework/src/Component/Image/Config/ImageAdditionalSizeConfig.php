<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config;

class ImageAdditionalSizeConfig
{
    protected ?int $width = null;

    protected ?int $height = null;

    protected string $media;

    /**
     * @param int|null $width
     * @param int|null $height
     * @param string $media
     */
    public function __construct(?int $width, ?int $height, string $media)
    {
        $this->width = $width;
        $this->height = $height;
        $this->media = $media;
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
