<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

class ImageSitemapItemImage
{
    public string $loc;

    public ?string $caption = null;

    public ?string $geoLocation = null;

    public string $title;

    public ?string $license = null;
}
