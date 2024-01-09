<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use Presta\SitemapBundle\Sitemap\Url\UrlConcrete as BaseUrlConcrete;
use Presta\SitemapBundle\Sitemap\Utils;

class UrlConcrete extends BaseUrlConcrete
{
    /**
     * @param string $loc
     */
    public function __construct(string $loc)
    {
        $this->setLoc($loc);
    }

    /**
     * @return string
     */
    public function toXml(): string
    {
        return '<url><loc>' . Utils::encode($this->getLoc()) . '</loc></url>';
    }
}
