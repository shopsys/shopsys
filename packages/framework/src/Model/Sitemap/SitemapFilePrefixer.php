<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

class SitemapFilePrefixer
{
    /**
     * @param int $domainId
     * @return string
     */
    public function getSitemapFilePrefixForDomain($domainId)
    {
        return 'domain_' . $domainId . '_sitemap';
    }
}
