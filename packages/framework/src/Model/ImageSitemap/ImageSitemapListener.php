<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

use Override;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\GoogleImage;
use Presta\SitemapBundle\Sitemap\Url\GoogleImageUrlDecorator;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ImageSitemapListener implements EventSubscriberInterface
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ImageSitemapFacade $imageSitemapFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ImageSitemapPopulateEvent::class => 'populateImageSitemap',
        ];
    }

    public function populateImageSitemap(ImageSitemapPopulateEvent $event): void
    {
        $domainId = (int)$event->getSection();

        /** @var \Presta\SitemapBundle\Service\AbstractGenerator $generator */
        $generator = $event->getUrlContainer();
        $generator->setDefaults([
            'priority' => null,
            'changefreq' => null,
            'lastmod' => null,
        ]);
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        foreach ($this->domain->getAllWithSameBaseUrl($domainConfig) as $relevantDomainConfig) {
            $this->populateForDomainConfig($relevantDomainConfig, $generator);
        }
    }

    protected function populateForDomainConfig(DomainConfig $domainConfig, UrlContainerInterface $generator): void
    {
        $productSitemapItems = $this->imageSitemapFacade->getImageSitemapItemsForVisibleProducts($domainConfig);
        $this->addUrlsBySitemapItems($productSitemapItems, $generator, $this->imageSitemapFacade->getProductsSectionName($domainConfig));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapItem[] $imageSitemapItems
     */
    protected function addUrlsBySitemapItems(
        array $imageSitemapItems,
        UrlContainerInterface $generator,
        string $section,
    ): void {
        foreach ($imageSitemapItems as $imageSitemapItem) {
            $urlConcrete = new UrlConcrete($imageSitemapItem->loc);
            $decoratedUrl = new GoogleImageUrlDecorator($urlConcrete);

            foreach ($imageSitemapItem->images as $imageSitemapItemImage) {
                $googleImage = new GoogleImage($imageSitemapItemImage->loc);
                $decoratedUrl->addImage($googleImage);
            }

            $generator->addUrl($decoratedUrl, $section);
        }
    }
}
