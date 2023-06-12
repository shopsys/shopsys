<?php

declare(strict_types=1);

namespace App\Model\Sitemap;

use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\AbstractGenerator;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapFacade;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapListener as BaseSitemapListener;

/**
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Model\Sitemap\SitemapFacade $sitemapFacade
 * @property \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
 */
class SitemapListener extends BaseSitemapListener
{
    /**
     * @param \App\Model\Sitemap\SitemapFacade $sitemapFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \App\Model\Sitemap\SitemapRepository $sitemapRepository
     */
    public function __construct(
        SitemapFacade $sitemapFacade,
        Domain $domain,
        DomainRouterFactory $domainRouterFactory,
        private SitemapRepository $sitemapRepository,
    ) {
        parent::__construct($sitemapFacade, $domain, $domainRouterFactory);
    }

    /**
     * @param \Presta\SitemapBundle\Event\SitemapPopulateEvent $event
     */
    public function populateSitemap(SitemapPopulateEvent $event): void
    {
        $section = $event->getSection();
        $domainId = (int)$section;

        /** @var \Presta\SitemapBundle\Service\AbstractGenerator $generator */
        $generator = $event->getUrlContainer();
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        $this->addUrlForHomepage($generator, $domainConfig, $section);

        $categorySitemapItems = $this->sitemapFacade->getSitemapItemsForVisibleCategories($domainConfig);
        $this->addUrlsForSitemapItems($categorySitemapItems, $generator, $domainConfig, 'categories');

        $categorySeoMixSitemapItems = $this->sitemapRepository->getSitemapItemsForVisibleCategorySeoMix($domainConfig);
        $this->addUrlsForSitemapItems($categorySeoMixSitemapItems, $generator, $domainConfig, 'filtersCategories');

        $productSitemapItems = $this->sitemapFacade->getSitemapItemsForListableProducts($domainConfig);
        $this->addUrlsForSitemapItems($productSitemapItems, $generator, $domainConfig, 'sellableProducts');

        $productSoldOutSitemapItems = $this->sitemapFacade->getSitemapItemsForSoldOutProducts($domainConfig);
        $this->addUrlsForSitemapItems($productSoldOutSitemapItems, $generator, $domainConfig, 'soldOutProducts');

        $articleSitemapItems = $this->sitemapFacade->getSitemapItemsForArticlesOnDomain($domainConfig);
        $this->addUrlsForSitemapItems($articleSitemapItems, $generator, $domainConfig, 'articles');

        $blogArticleSitemapItems = $this->sitemapFacade->getSitemapItemsForBlogArticlesOnDomain($domainConfig);
        $this->addUrlsForSitemapItems($blogArticleSitemapItems, $generator, $domainConfig, 'articles');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[] $sitemapItems
     * @param \Presta\SitemapBundle\Service\AbstractGenerator $generator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $section
     */
    private function addUrlsForSitemapItems(
        array $sitemapItems,
        AbstractGenerator $generator,
        DomainConfig $domainConfig,
        string $section,
    ): void {
        foreach ($sitemapItems as $sitemapItem) {
            $absoluteUrl = $this->getAbsoluteUrlByDomainConfigAndSlug($domainConfig, $sitemapItem->slug);
            $urlConcrete = new UrlConcrete($absoluteUrl);

            $generator->addUrl($urlConcrete, $section);
        }
    }

    /**
     * @param \Presta\SitemapBundle\Service\AbstractGenerator $generator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $section
     */
    private function addUrlForHomepage(
        AbstractGenerator $generator,
        DomainConfig $domainConfig,
        string $section,
    ): void {
        $urlConcrete = new UrlConcrete($domainConfig->getUrl());

        $generator->addUrl($urlConcrete, $section);
    }
}
