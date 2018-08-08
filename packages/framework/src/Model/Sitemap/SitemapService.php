<?php

namespace Shopsys\FrameworkBundle\Model\Sitemap;

class SitemapService
{
    public function getSitemapFilePrefixForDomain(int $domainId): string
    {
        return 'domain_' . $domainId . '_sitemap';
    }
}
