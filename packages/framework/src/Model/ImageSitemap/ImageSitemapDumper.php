<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

use Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumper;

class ImageSitemapDumper extends SitemapDumper
{
    /**
     * @param string|null $section
     */
    protected function populate(?string $section = null): void
    {
        $event = new ImageSitemapPopulateEvent($this, $section);

        $this->dispatcher->dispatch($event);
    }
}
