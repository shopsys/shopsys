<?php

declare(strict_types=1);

namespace App\Model\Sitemap;

use App\Component\Domain\Domain;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapFacade;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapListener as BaseSitemapListener;

/**
 * @property \App\Component\Domain\Domain $domain
 * @property \App\Model\Sitemap\SitemapFacade $sitemapFacade
 */
class SitemapListener extends BaseSitemapListener
{
    private const PRIORITY_CATEGORY_SEO_MIX = 0.9;

    /**
     * @var \App\Model\Sitemap\SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @param \App\Model\Sitemap\SitemapFacade $sitemapFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \App\Model\Sitemap\SitemapRepository $sitemapRepository
     */
    public function __construct(
        SitemapFacade $sitemapFacade,
        Domain $domain,
        DomainRouterFactory $domainRouterFactory,
        SitemapRepository $sitemapRepository
    ) {
        $this->sitemapRepository = $sitemapRepository;

        parent::__construct($sitemapFacade, $domain, $domainRouterFactory);
    }

    /**
     * @param \Presta\SitemapBundle\Event\SitemapPopulateEvent $event
     */
    public function populateSitemap(SitemapPopulateEvent $event)
    {
        $section = $event->getSection();
        $domainId = (int)$section;

        $generator = $event->getUrlContainer();
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        $this->addHomepageUrl($generator, $domainConfig, $section, static::PRIORITY_HOMEPAGE);

        $categorySitemapItems = $this->sitemapFacade->getSitemapItemsForVisibleCategories($domainConfig);
        $this->addUrlsBySitemapItems($categorySitemapItems, $generator, $domainConfig, 'categories', static::PRIORITY_CATEGORIES);

        $categorySeoMixSitemapItems = $this->sitemapRepository->getSitemapItemsForVisibleCategorySeoMix($domainConfig);
        $this->addUrlsBySitemapItems($categorySeoMixSitemapItems, $generator, $domainConfig, 'filtersCategories', static::PRIORITY_CATEGORY_SEO_MIX);

        $productSitemapItems = $this->sitemapFacade->getSitemapItemsForVisibleProducts($domainConfig);
        $this->addUrlsBySitemapItems($productSitemapItems, $generator, $domainConfig, 'sellableProducts', static::PRIORITY_PRODUCTS);

        $productSoldOutSitemapItems = $this->sitemapFacade->getSitemapItemsForSoldOutProducts($domainConfig);
        $this->addUrlsBySitemapItems($productSoldOutSitemapItems, $generator, $domainConfig, 'soldOutProducts', static::PRIORITY_PRODUCTS);

        $articleSitemapItems = $this->sitemapFacade->getSitemapItemsForArticlesOnDomain($domainConfig);
        $this->addUrlsBySitemapItems($articleSitemapItems, $generator, $domainConfig, 'articles', static::PRIORITY_ARTICLES);

        $blogArticleSitemapItems = $this->sitemapFacade->getSitemapItemsForBlogArticlesOnDomain($domainConfig);
        $this->addUrlsBySitemapItems($blogArticleSitemapItems, $generator, $domainConfig, 'articles', static::PRIORITY_ARTICLES);

        $productSeriesSitemapItems = $this->sitemapFacade->getSitemapItemsForProductSeries($domainConfig);
        $this->addUrlsBySitemapItems($productSeriesSitemapItems, $generator, $domainConfig, 'series', static::PRIORITY_ARTICLES);

        $productSeriesCategorySitemapItems = $this->sitemapFacade->getSitemapItemsForProductSeriesCategory($domainConfig);
        $this->addUrlsBySitemapItems($productSeriesCategorySitemapItems, $generator, $domainConfig, 'series', static::PRIORITY_ARTICLES);
    }
}
