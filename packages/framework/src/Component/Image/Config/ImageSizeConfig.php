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
     * @param int $width
     * @param int $height
     * @param bool $crop
     * @param string|null $occurrence
     */
    public function __construct($name, $width, $height, $crop, $occurrence)
    {
        $this->name = $name;
        $this->width = $width;
        $this->height = $height;
        $this->crop = $crop;
        $this->occurrence = $occurrence;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getCrop()
    {
        return $this->crop;
    }

    /**
     * @return string|null
     */
    public function getOccurrence()
    {
        return $this->occurrence;
    }
}
