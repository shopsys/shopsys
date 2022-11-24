<?php

namespace Shopsys\FrameworkBundle\Model\Sitemap;

class SitemapFilePrefixer
{
    /**
     * @param int $domainId
     * @return string
     */
    public function getSitemapFilePrefixForDomain(int $domainId): string
    {
        return 'domain_' . $domainId . '_sitemap';
    }
}
