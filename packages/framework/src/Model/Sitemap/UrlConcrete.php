<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use Override;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete as BaseUrlConcrete;
use Presta\SitemapBundle\Sitemap\Utils;

class UrlConcrete extends BaseUrlConcrete
{
    public function __construct(string $loc)
    {
        $this->setLoc($loc);
    }

    #[Override]
    public function toXml(): string
    {
        return '<url><loc>' . Utils::encode($this->getLoc()) . '</loc></url>';
    }
}
