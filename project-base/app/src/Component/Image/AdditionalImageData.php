<?php

declare(strict_types=1);

namespace App\Component\Image;

use Shopsys\FrameworkBundle\Component\Image\AdditionalImageData as BaseAdditionalImageData;

class AdditionalImageData extends BaseAdditionalImageData
{
    /**
     * @var int|null
     */
    private ?int $width;

    /**
     * @var int|null
     */
    private ?int $height;

    /**
     * @param string $media
     * @param string $url
     * @param int|null $width
     * @param int|null $height
     */
    public function __construct(string $media, string $url, ?int $width = null, ?int $height = null)
    {
        parent::__construct($media, $url);

        $this->width = $width;
        $this->height = $height;
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
}
