<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

class FeedItemImageHelper
{
    protected const DEFAULT_IMAGE_SIZE = 605;

    /**
     * @param string $imageUrl
     * @param int|null $width
     * @return string
     */
    public static function limitWidthInImageUrl(string $imageUrl, ?int $width = null): string
    {
        return $imageUrl . '?width=' . ($width ?? static::DEFAULT_IMAGE_SIZE);
    }
}
