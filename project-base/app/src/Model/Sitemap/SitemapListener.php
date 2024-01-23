<?php

declare(strict_types=1);

namespace App\Model\Sitemap;

use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapListener as BaseSitemapListener;

/**
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Model\Sitemap\SitemapFacade $sitemapFacade
 * @property \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
 * @method __construct(\App\Model\Sitemap\SitemapFacade $sitemapFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory, \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade, \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade)
 */
class SitemapListener extends BaseSitemapListener
{
    /**
     * @param \Presta\SitemapBundle\Event\SitemapPopulateEvent $event
     */
    public function populateSitemap(SitemapPopulateEvent $event): void
    {
        parent::populateSitemap($event);

        $section = $event->getSection();
        $domainId = (int)$section;

        /** @var \Presta\SitemapBundle\Service\AbstractGenerator $generator */
        $generator = $event->getUrlContainer();
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        $categorySeoMixSitemapItems = $this->sitemapFacade->getSitemapItemsForVisibleCategorySeoMix($domainConfig);
        $this->addUrlsForSitemapItems(
            $categorySeoMixSitemapItems,
            $generator,
            $domainConfig,
            'filtersCategories',
        );
    }
}
