<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config;

class ImageSizeConfig
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var bool
     */
    private $crop;

    /**
     * @var string|null
     */
    private $occurrence;

    /**
     * @param string|null $name
     * @param string|null $occurrence
     */
    public function __construct(?string $name, int $width, int $height, bool $crop, ?string $occurrence)
    {
        $this->name = $name;
        $this->width = $width;
        $this->height = $height;
        $this->crop = $crop;
        $this->occurrence = $occurrence;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getCrop(): int
    {
        return $this->crop;
    }

    public function getOccurrence(): ?string
    {
        return $this->occurrence;
    }
}
