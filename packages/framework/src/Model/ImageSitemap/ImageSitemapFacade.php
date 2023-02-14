<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ImageSitemapFacade
{
    /**
     * @var string
     */
    protected string $sitemapsDir;

    /**
     * @var string
     */
    protected string $sitemapsUrlPrefix;

    /**
     * @param string $sitemapsDir
     * @param string $sitemapsUrlPrefix
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapDumperFactory $imageSitemapDumperFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(
        string $sitemapsDir,
        string $sitemapsUrlPrefix,
        protected readonly Domain $domain,
        protected readonly ImageSitemapDumperFactory $imageSitemapDumperFactory,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ImageFacade $imageFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ProductRepository $productRepository,
        protected readonly EntityManagerInterface $entityManager
    ) {
        $this->sitemapsDir = $sitemapsDir;
        $this->sitemapsUrlPrefix = $sitemapsUrlPrefix;
    }

    public function generateForAllDomains(): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $this->entityManager->clear();  // For load all translations correctly, we must run clear.
            $section = (string)$domainConfig->getId();

            $domainSitemapDumper = $this->imageSitemapDumperFactory->createForImagesForDomain($domainConfig->getId());
            $domainSitemapDumper->dump(
                $this->sitemapsDir,
                $domainConfig->getUrl() . $this->sitemapsUrlPrefix . '/',
                $section
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapItem[]
     */
    public function getImageSitemapItemsForVisibleProducts(DomainConfig $domainConfig): array
    {
        $imageSitemapItems = [];
        $domainId = $domainConfig->getId();
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product[] $products */
        $products = $this->productRepository->getAllOfferedProducts($domainId, $pricingGroup);

        foreach ($products as $product) {
            try {
                $imageUrl = $this->imageFacade->getImageUrl($domainConfig, $product);
                $imageSitemapItem = new ImageSitemapItem();
                $imageSitemapItem->loc = $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId($domainConfig->getId(), 'front_product_detail', $product->getId());

                $sitemapImage = new ImageSitemapItemImage();
                $sitemapImage->loc = $imageUrl;
                $sitemapImage->title = $product->getName($domainConfig->getLocale());
                $imageSitemapItem->images[] = $sitemapImage;

                $imageSitemapItems[] = $imageSitemapItem;
            } catch (ImageNotFoundException $imageNotFoundException) {
                continue;
            }
        }
        return $imageSitemapItems;
    }
}
