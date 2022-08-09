<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageAdditionalSizeNotFoundException;

class ImageSizeConfig
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var int|null
     */
    protected $width;

    /**
     * @var int|null
     */
    protected $height;

    /**
     * @var bool
     */
    protected $crop;

    /**
     * @var string|null
     */
    protected $occurrence;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageAdditionalSizeConfig[]
     */
    protected $additionalSizes;

    /**
     * @param string|null $name
     * @param int|null $width
     * @param int|null $height
     * @param bool $crop
     * @param string|null $occurrence
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageAdditionalSizeConfig[] $additionalSizes
     */
    public function __construct(?string $name, ?int $width, ?int $height, bool $crop, ?string $occurrence, array $additionalSizes)
    {
        $this->name = $name;
        $this->width = $width;
        $this->height = $height;
        $this->crop = $crop;
        $this->occurrence = $occurrence;
        $this->additionalSizes = $additionalSizes;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
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
     * @return bool
     */
    public function getCrop(): bool
    {
        return $this->crop;
    }

    /**
     * @return string|null
     */
    public function getOccurrence(): ?string
    {
        return $this->occurrence;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageAdditionalSizeConfig[]
     */
    public function getAdditionalSizes(): array
    {
        return $this->additionalSizes;
    }

    /**
     * @param int $additionalIndex
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageAdditionalSizeConfig
     */
    public function getAdditionalSize(int $additionalIndex): ImageAdditionalSizeConfig
    {
        if (!isset($this->additionalSizes[$additionalIndex])) {
            throw new ImageAdditionalSizeNotFoundException($this->name, $additionalIndex);
        }

        return $this->additionalSizes[$additionalIndex];
    }
}
