<?php

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\AbstractGenerator;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapListener implements EventSubscriberInterface
{
    protected const PRIORITY_NONE = null;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapFacade $sitemapFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        protected readonly SitemapFacade $sitemapFacade,
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPopulateEvent::class => 'populateSitemap',
        ];
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
        $generator->setDefaults([
            'priority' => static::PRIORITY_NONE,
            'changefreq' => null,
            'lastmod' => null,
        ]);
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        $this->addHomepageUrl($generator, $domainConfig, $section, static::PRIORITY_NONE);

        $productSitemapItems = $this->sitemapFacade->getSitemapItemsForListableProducts($domainConfig);
        $this->addUrlsBySitemapItems(
            $productSitemapItems,
            $generator,
            $domainConfig,
            $section,
            static::PRIORITY_NONE,
        );

        $categorySitemapItems = $this->sitemapFacade->getSitemapItemsForVisibleCategories($domainConfig);
        $this->addUrlsBySitemapItems(
            $categorySitemapItems,
            $generator,
            $domainConfig,
            $section,
            static::PRIORITY_NONE,
        );

        $articleSitemapItems = $this->sitemapFacade->getSitemapItemsForArticlesOnDomain($domainConfig);
        $this->addUrlsBySitemapItems(
            $articleSitemapItems,
            $generator,
            $domainConfig,
            $section,
            static::PRIORITY_NONE,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[] $sitemapItems
     * @param \Presta\SitemapBundle\Service\AbstractGenerator $generator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $section
     * @param float|string|int|null $elementPriority
     */
    protected function addUrlsBySitemapItems(
        array $sitemapItems,
        AbstractGenerator $generator,
        DomainConfig $domainConfig,
        $section,
        $elementPriority,
    ) {
        foreach ($sitemapItems as $sitemapItem) {
            $absoluteUrl = $this->getAbsoluteUrlByDomainConfigAndSlug($domainConfig, $sitemapItem->slug);
            $urlConcrete = new UrlConcrete($absoluteUrl, null, null, $elementPriority);
            $generator->addUrl($urlConcrete, $section);
        }
    }

    /**
     * @param \Presta\SitemapBundle\Service\AbstractGenerator $generator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $section
     * @param float|string|int|null  $elementPriority
     */
    protected function addHomepageUrl(
        AbstractGenerator $generator,
        DomainConfig $domainConfig,
        $section,
        $elementPriority,
    ) {
        $domainRouter = $this->domainRouterFactory->getRouter($domainConfig->getId());
        $absoluteUrl = $domainRouter->generate('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urlConcrete = new UrlConcrete($absoluteUrl, null, null, $elementPriority);
        $generator->addUrl($urlConcrete, $section);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $slug
     * @return string
     */
    protected function getAbsoluteUrlByDomainConfigAndSlug(DomainConfig $domainConfig, $slug)
    {
        return $domainConfig->getUrl() . '/' . $slug;
    }
}
