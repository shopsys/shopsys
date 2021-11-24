<?php

declare(strict_types=1);

namespace App\Model\ImageSitemap;

class ImageSitemapItem
{
    /**
     * @var string
     */
    public string $loc;

    /**
     * @var \App\Model\ImageSitemap\ImageSitemapItemImage[]
     */
    public array $images;
}
