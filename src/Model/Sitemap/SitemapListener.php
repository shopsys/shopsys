<?php

declare(strict_types=1);

namespace App\Model\Sitemap;

use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapFacade;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapListener as BaseSitemapListener;

/**
 * @property \App\Component\Domain\Domain $domain
 */
class SitemapListener extends BaseSitemapListener
{
    private const PRIORITY_CATEGORY_SEO_MIX = 0.9;

    /**
     * @var \App\Model\Sitemap\SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapFacade $sitemapFacade
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
        parent::populateSitemap($event);

        $section = $event->getSection();
        $domainId = (int)$section;

        $generator = $event->getUrlContainer();
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        $categorySeoMixSitemapItems = $this->sitemapRepository->getSitemapItemsForVisibleCategorySeoMix($domainConfig);
        $this->addUrlsBySitemapItems($categorySeoMixSitemapItems, $generator, $domainConfig, $section, static::PRIORITY_CATEGORY_SEO_MIX);
    }
}
