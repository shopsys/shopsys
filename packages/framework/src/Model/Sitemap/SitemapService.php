<?php

namespace Shopsys\FrameworkBundle\Model\Sitemap;

class SitemapService
{
    /**
     * @param int $domainId
     */
    public function getSitemapFilePrefixForDomain($domainId): string
    {
        return 'domain_' . $domainId . '_sitemap';
    }
}
