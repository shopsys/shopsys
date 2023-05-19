<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config;

class ImageAdditionalSizeConfig
{
    /**
     * @param string $media
     * @param int|null $width
     * @param int|null $height
     */
    public function __construct(
        protected readonly string $media,
        protected readonly ?int $width = null,
        protected readonly ?int $height = null,
    ) {
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
