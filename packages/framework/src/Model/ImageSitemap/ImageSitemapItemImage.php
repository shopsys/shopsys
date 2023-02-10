<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

class ImageSitemapItemImage
{
    /**
     * @var string
     */
    public string $loc;

    /**
     * @var string|null
     */
    public ?string $caption = null;

    /**
     * @var string|null
     */
    public ?string $geoLocation = null;

    /**
     * @var string
     */
    public string $title;

    /**
     * @var string|null
     */
    public ?string $license = null;
}
