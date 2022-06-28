<?php

declare(strict_types=1);

namespace App\Model\Sitemap;

use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\AbstractGenerator;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
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
    private const PRIORITY_CATEGORY_SEO_MIX = 0.9;

    /**
     * @var \App\Model\Sitemap\SitemapRepository
     */
    private $sitemapRepository;

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

        /** @var \Presta\SitemapBundle\Service\AbstractGenerator $generator */
        $generator = $event->getUrlContainer();
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        $this->addHomepageUrl($generator, $domainConfig, $section, static::PRIORITY_HOMEPAGE);

        $categorySitemapItems = $this->sitemapFacade->getSitemapItemsForVisibleCategories($domainConfig);
        $this->addUrlsBySitemapItems($categorySitemapItems, $generator, $domainConfig, 'categories', static::PRIORITY_CATEGORIES);

        $categorySeoMixSitemapItems = $this->sitemapRepository->getSitemapItemsForVisibleCategorySeoMix($domainConfig);
        // @phpstan-ignore-next-line Wrong annotation in parent class
        $this->addUrlsBySitemapItems($categorySeoMixSitemapItems, $generator, $domainConfig, 'filtersCategories', self::PRIORITY_CATEGORY_SEO_MIX);

        $productSitemapItems = $this->sitemapFacade->getSitemapItemsForVisibleProducts($domainConfig);
        $this->addUrlsBySitemapItems($productSitemapItems, $generator, $domainConfig, 'sellableProducts', static::PRIORITY_PRODUCTS);

        $productSoldOutSitemapItems = $this->sitemapFacade->getSitemapItemsForSoldOutProducts($domainConfig);
        $this->addUrlsBySitemapItems($productSoldOutSitemapItems, $generator, $domainConfig, 'soldOutProducts', static::PRIORITY_PRODUCTS);

        $articleSitemapItems = $this->sitemapFacade->getSitemapItemsForArticlesOnDomain($domainConfig);
        $this->addUrlsBySitemapItems($articleSitemapItems, $generator, $domainConfig, 'articles', static::PRIORITY_ARTICLES);

        $blogArticleSitemapItems = $this->sitemapFacade->getSitemapItemsForBlogArticlesOnDomain($domainConfig);
        $this->addUrlsBySitemapItems($blogArticleSitemapItems, $generator, $domainConfig, 'articles', static::PRIORITY_ARTICLES);
    }

    /**
     * @param \Presta\SitemapBundle\Service\AbstractGenerator $generator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $section
     * @param int $elementPriority
     */
    protected function addHomepageUrl(
        AbstractGenerator $generator,
        DomainConfig $domainConfig,
        $section,
        $elementPriority
    ) {
        $urlConcrete = new UrlConcrete($domainConfig->getUrl(), null, null, $elementPriority);
        $generator->addUrl($urlConcrete, $section);
    }
}
