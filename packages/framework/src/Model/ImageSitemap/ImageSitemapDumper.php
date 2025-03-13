<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

use Override;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumper;

class ImageSitemapDumper extends SitemapDumper
{
    /**
     * @param string|null $section
     */
    #[Override]
    protected function populate(?string $section = null): void
    {
        $event = new ImageSitemapPopulateEvent($this, $this->urlGenerator, $section);

        $this->dispatcher->dispatch($event);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapIndex
     */
    #[Override]
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
