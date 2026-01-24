<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

class ImageSitemapFilePrefixer
{
    public function getSitemapFilePrefixForDomain(int $domainId): string
    {
        return 'domain_' . $domainId . '_sitemap_image';
    }
}
