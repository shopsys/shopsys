<?php

declare(strict_types=1);

namespace App\Model\ImageSitemap;

use App\Component\Image\ImageFacade;
use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Blog\Article\BlogArticleFacade;
use App\Model\Product\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class ImageSitemapFacade
{
    /**
     * @var string
     */
    private string $sitemapsDir;

    /**
     * @var string
     */
    private string $sitemapsUrlPrefix;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Model\ImageSitemap\ImageSitemapDumperFactory
     */
    private ImageSitemapDumperFactory $imageSitemapDumperFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    private PricingGroupSettingFacade $pricingGroupSettingFacade;

    /**
     * @var \App\Component\Image\ImageFacade
     */
    private ImageFacade $imageFacade;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @var \App\Model\Blog\Article\BlogArticleFacade
     */
    private BlogArticleFacade $blogArticleFacade;

    /**
     * @var \App\Model\Product\ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param string $sitemapsDir
     * @param string $sitemapsUrlPrefix
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\ImageSitemap\ImageSitemapDumperFactory $imageSitemapDumperFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(
        string $sitemapsDir,
        string $sitemapsUrlPrefix,
        Domain $domain,
        ImageSitemapDumperFactory $imageSitemapDumperFactory,
        PricingGroupSettingFacade $pricingGroupSettingFacade,
        ImageFacade $imageFacade,
        FriendlyUrlFacade $friendlyUrlFacade,
        BlogArticleFacade $blogArticleFacade,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->sitemapsDir = $sitemapsDir;
        $this->sitemapsUrlPrefix = $sitemapsUrlPrefix;
        $this->domain = $domain;
        $this->imageSitemapDumperFactory = $imageSitemapDumperFactory;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
        $this->imageFacade = $imageFacade;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->blogArticleFacade = $blogArticleFacade;
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
    }

    public function generateForAllDomains(): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $this->entityManager->clear();  // For load all translations correctly, we must run clear.
            $this->imageFacade->clearImageCache(); // For correctly image url, we must run cache clear.
            $section = (string)$domainConfig->getId();

            $domainSitemapDumper = $this->imageSitemapDumperFactory->createForImagesForDomain($domainConfig->getId());
            $domainSitemapDumper->dump(
                $this->sitemapsDir,
                $domainConfig->getUrl() . $this->sitemapsUrlPrefix . '/',
                $section
            );
        }
        $this->imageFacade->clearImageCache();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\ImageSitemap\ImageSitemapItem[]
     */
    public function getImageSitemapItemsForBlogArticlesOnDomain(DomainConfig $domainConfig): array
    {
        $imageSitemapItems = [];
        $blogArticles = $this->blogArticleFacade->getAllVisibleOnDomain($domainConfig);
        foreach ($blogArticles as $blogArticle) {
            try {
                $imageUrl = $this->imageFacade->getImageUrl($domainConfig, $blogArticle);
                $imageSitemapItem = new ImageSitemapItem();
                $imageSitemapItem->loc = $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId($domainConfig->getId(), 'front_blogarticle_detail', $blogArticle->getId());

                $sitemapImage = new ImageSitemapItemImage();
                $sitemapImage->loc = $imageUrl;
                $sitemapImage->title = $blogArticle->getName($domainConfig->getLocale()) ?? '';
                $imageSitemapItem->images[] = $sitemapImage;

                $imageSitemapItems[] = $imageSitemapItem;
            } catch (ImageNotFoundException $imageNotFoundException) {
                continue;
            }
        }

        return $imageSitemapItems;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\ImageSitemap\ImageSitemapItem[]
     */
    public function getImageSitemapItemsForVisibleProducts(DomainConfig $domainConfig): array
    {
        $imageSitemapItems = [];
        $domainId = $domainConfig->getId();
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        /** @var \App\Model\Product\Product[] $products */
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
