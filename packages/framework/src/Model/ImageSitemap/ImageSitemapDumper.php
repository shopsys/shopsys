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

    /**
     * @return \Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapIndex
     */
    protected function getRoot(): ImageSitemapIndex
    {
        if ($this->root === null) {
            $this->root = new ImageSitemapIndex();

            foreach ($this->urlsets as $urlset) {
                $this->root->addSitemap($urlset);
            }
        }

        /** @var \Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapIndex $root */
        $root = $this->root;

        return $root;
    }
}
