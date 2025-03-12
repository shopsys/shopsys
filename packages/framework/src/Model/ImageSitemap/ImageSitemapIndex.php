<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

use Override;
use Presta\SitemapBundle\Sitemap\Sitemapindex as BaseSitemapIndex;
use Presta\SitemapBundle\Sitemap\Urlset;

class ImageSitemapIndex extends BaseSitemapIndex
{
    /**
     * @param \Presta\SitemapBundle\Sitemap\Urlset $urlset
     * @return string
     */
    #[Override]
    protected function getSitemapXml(Urlset $urlset): string
    {
        return '<sitemap><loc>' . $urlset->getLoc() . '</loc></sitemap>';
    }
}
