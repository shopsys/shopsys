<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

class SitemapFilePrefixer
{
    public function getSitemapFilePrefixForDomain(int $domainId): string
    {
        return 'domain_' . $domainId . '_sitemap';
    }
}
