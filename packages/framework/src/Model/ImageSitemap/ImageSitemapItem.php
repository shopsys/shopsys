<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

class ImageSitemapItem
{
    public string $loc;

    /**
     * @var \Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapItemImage[]
     */
    public array $images;
}
