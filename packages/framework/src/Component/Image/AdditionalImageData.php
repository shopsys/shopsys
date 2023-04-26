<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

class AdditionalImageData
{
    /**
     * @param string $media
     * @param string $url
     */
    public function __construct(
        public readonly string $media,
        public readonly string $url,
    ) {
    }
}
