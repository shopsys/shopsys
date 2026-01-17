<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

class ImageUrlWithSizeHelper
{
    protected const int DEFAULT_IMAGE_SIZE = 605;

    public function limitSizeInImageUrl(string $imageUrl, ?int $width = null, ?int $height = null): string
    {
        return $imageUrl . '?width=' . ($width ?? static::DEFAULT_IMAGE_SIZE) . ($height !== null ? '&height=' . $height : '');
    }
}
