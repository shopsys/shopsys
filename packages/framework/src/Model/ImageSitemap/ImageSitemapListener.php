<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\GoogleImage;
use Presta\SitemapBundle\Sitemap\Url\GoogleImageUrlDecorator;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ImageSitemapListener implements EventSubscriberInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapFacade $sitemapFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ImageSitemapFacade $sitemapFacade
    ) {
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ImageSitemapPopulateEvent::class => 'populateImageSitemap',
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapPopulateEvent $event
     */
    public function populateImageSitemap(ImageSitemapPopulateEvent $event)
    {
        $section = $event->getSection();
        $domainId = (int)$section;

        /** @var \Presta\SitemapBundle\Service\AbstractGenerator $generator */
        $generator = $event->getUrlContainer();
        $generator->setDefaults([
            'priority' => null,
            'changefreq' => null,
            'lastmod' => null,
        ]);
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        $productSitemapItems = $this->sitemapFacade->getImageSitemapItemsForVisibleProducts($domainConfig);
        $this->addUrlsBySitemapItems($productSitemapItems, $generator, 'products');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapItem[] $imageSitemapItems
     * @param \Presta\SitemapBundle\Service\UrlContainerInterface $generator
     * @param string $section
     */
    protected function addUrlsBySitemapItems(array $imageSitemapItems, UrlContainerInterface $generator, string $section): void
    {
        foreach ($imageSitemapItems as $imageSitemapItem) {
            $urlConcrete = new UrlConcrete($imageSitemapItem->loc);
            $decoratedUrl = new GoogleImageUrlDecorator($urlConcrete);

            foreach ($imageSitemapItem->images as $imageSitemapItemImage) {
                $googleImage = new GoogleImage(
                    $imageSitemapItemImage->loc,
                    $imageSitemapItemImage->caption,
                    $imageSitemapItemImage->geoLocation,
                    $imageSitemapItemImage->title,
                    $imageSitemapItemImage->license
                );
                $decoratedUrl->addImage($googleImage);
            }

            $generator->addUrl($decoratedUrl, $section);
        }
    }
}
