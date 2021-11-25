<?php

declare(strict_types=1);

namespace App\Model\ImageSitemap;

use Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumper;

class ImageSitemapDumper extends SitemapDumper
{
    /**
     * @param mixed|null $section
     */
    protected function populate($section = null)
    {
        $event = new ImageSitemapPopulateEvent($this, $section);

        $this->dispatcher->dispatch($event);
    }
}
