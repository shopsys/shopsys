<?php

declare(strict_types=1);

namespace App\Model\ImageSitemap;

class ImageSitemapFilePrefixer
{
    /**
     * @param int $domainId
     * @return string
     */
    public function getSitemapFilePrefixForDomain($domainId): string
    {
        return 'domain_' . $domainId . '_sitemap_image';
    }
}
