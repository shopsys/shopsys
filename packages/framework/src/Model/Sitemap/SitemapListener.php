<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use Override;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\AbstractGenerator;
use Presta\SitemapBundle\Sitemap\Url\GoogleMultilangUrlDecorator;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SitemapListener implements EventSubscriberInterface
{
    public function __construct(
        protected readonly SitemapFacade $sitemapFacade,
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly SeoSettingFacade $seoSettingFacade,
    ) {
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPopulateEvent::class => 'populateSitemap',
        ];
    }

    public function populateSitemap(SitemapPopulateEvent $event): void
    {
        $section = $event->getSection();
        $domainId = (int)$section;

        /** @var \Presta\SitemapBundle\Service\AbstractGenerator $generator */
        $generator = $event->getUrlContainer();
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        foreach ($this->domain->getAllWithSameBaseUrl($domainConfig) as $relevantDomainConfig) {
            $this->populateForDomainConfig($relevantDomainConfig, $generator);
        }
    }

    protected function populateForDomainConfig(DomainConfig $domainConfig, AbstractGenerator $generator): void
    {
        $this->addUrlForHomepage($generator, $domainConfig);

        $categorySitemapItems = $this->sitemapFacade->getSitemapItemsForVisibleCategories($domainConfig);
        $this->addUrlsForSitemapItemsWithAlternativeLocations(
            $categorySitemapItems,
            $generator,
            $domainConfig,
            'categories',
            'front_product_list',
            $this->sitemapFacade->getSitemapItemsForVisibleCategories(...),
        );

        $productSitemapItems = $this->sitemapFacade->getSitemapItemsForListableProducts($domainConfig);
        $this->addUrlsForSitemapItemsWithAlternativeLocations(
            $productSitemapItems,
            $generator,
            $domainConfig,
            'sellableProducts',
            'front_product_detail',
            $this->sitemapFacade->getSitemapItemsForListableProducts(...),
        );

        $productSoldOutSitemapItems = $this->sitemapFacade->getSitemapItemsForSoldOutProducts($domainConfig);
        $this->addUrlsForSitemapItemsWithAlternativeLocations(
            $productSoldOutSitemapItems,
            $generator,
            $domainConfig,
            'soldOutProducts',
            'front_product_detail',
            $this->sitemapFacade->getSitemapItemsForSoldOutProducts(...),
        );

        $articleSitemapItems = $this->sitemapFacade->getSitemapItemsForArticlesOnDomain($domainConfig);
        $this->addUrlsForSitemapItems(
            $articleSitemapItems,
            $generator,
            $domainConfig,
            'articles',
        );

        $blogArticleSitemapItems = $this->sitemapFacade->getSitemapItemsForBlogArticlesOnDomain($domainConfig);
        $this->addUrlsForSitemapItemsWithAlternativeLocations(
            $blogArticleSitemapItems,
            $generator,
            $domainConfig,
            'articles',
            'front_blogarticle_detail',
            $this->sitemapFacade->getSitemapItemsForBlogArticlesOnDomain(...),
        );

        $flagSitemapItems = $this->sitemapFacade->getSitemapItemsForVisibleFlags($domainConfig);
        $this->addUrlsForSitemapItemsWithAlternativeLocations(
            $flagSitemapItems,
            $generator,
            $domainConfig,
            'flags',
            'front_flag_detail',
            $this->sitemapFacade->getSitemapItemsForVisibleFlags(...),
        );

        $categorySeoMixSitemapItems = $this->sitemapFacade->getSitemapItemsForVisibleCategorySeoMix($domainConfig);
        $this->addUrlsForSitemapItems(
            $categorySeoMixSitemapItems,
            $generator,
            $domainConfig,
            'filtersCategories',
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[] $sitemapItems
     */
    protected function addUrlsForSitemapItems(
        array $sitemapItems,
        AbstractGenerator $generator,
        DomainConfig $domainConfig,
        string $section,
    ): void {
        foreach ($sitemapItems as $sitemapItem) {
            $absoluteUrl = $this->getAbsoluteUrlByDomainConfigAndSlug($domainConfig, $sitemapItem->slug);
            $urlConcrete = new UrlConcrete($absoluteUrl);

            $generator->addUrl($urlConcrete, $this->sitemapFacade->getSectionNameForDomainConfig($section, $domainConfig));
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[] $sitemapItems
     */
    protected function addUrlsForSitemapItemsWithAlternativeLocations(
        array $sitemapItems,
        AbstractGenerator $generator,
        DomainConfig $domainConfig,
        string $section,
        string $routeName,
        callable $getAlternativeForDomainCallable,
    ): void {
        $alternativeDomainIds = $this->seoSettingFacade->getAlternativeDomainsForDomain($domainConfig->getId());

        $alternativesByDomainId = [];

        foreach ($alternativeDomainIds as $alternativeDomainId) {
            $data = $getAlternativeForDomainCallable($this->domain->getDomainConfigById($alternativeDomainId));
            $alternativesByDomainId[$alternativeDomainId] = array_column($data, 'id');
        }

        foreach ($sitemapItems as $sitemapItem) {
            $absoluteUrl = $this->getAbsoluteUrlByDomainConfigAndSlug($domainConfig, $sitemapItem->slug);
            $urlConcrete = new UrlConcrete($absoluteUrl);

            $multilingualUrl = new GoogleMultilangUrlDecorator($urlConcrete);

            foreach ($alternativeDomainIds as $alternativeDomainId) {
                if (in_array($sitemapItem->id, $alternativesByDomainId[$alternativeDomainId], true)) {
                    $hrefLangLink = $this->hreflangLinksFacade->createHreflangLink($alternativeDomainId, $routeName, $sitemapItem->id);
                    $multilingualUrl->addLink($hrefLangLink->href, $hrefLangLink->hreflang);
                }
            }

            $generator->addUrl($multilingualUrl, $this->sitemapFacade->getSectionNameForDomainConfig($section, $domainConfig));
        }
    }

    protected function addUrlForHomepage(
        AbstractGenerator $generator,
        DomainConfig $domainConfig,
    ): void {
        $urlConcrete = new UrlConcrete($domainConfig->getUrl());
        $multilingualUrl = new GoogleMultilangUrlDecorator($urlConcrete);

        $alternativeDomainIds = $this->seoSettingFacade->getAlternativeDomainsForDomain($domainConfig->getId());

        foreach ($alternativeDomainIds as $alternativeDomainId) {
            $domain = $this->domain->getDomainConfigById($alternativeDomainId);
            $multilingualUrl->addLink($domain->getUrl(), $domain->getLocale());
        }

        $generator->addUrl($multilingualUrl, $this->sitemapFacade->getSectionNameForDomainConfig('homepage', $domainConfig));
    }

    protected function getAbsoluteUrlByDomainConfigAndSlug(DomainConfig $domainConfig, string $slug): string
    {
        return $domainConfig->getUrl() . '/' . $slug;
    }
}
