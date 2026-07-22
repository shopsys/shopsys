<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade;
use Shopsys\ProductFeed\HeurekaBundle\Model\Setting\HeurekaFeedSettingEnum;

class HeurekaFeedItemFactory
{
    protected const string HEUREKA_CATEGORY_FULL_NAMES_CACHE_NAMESPACE = 'heurekaCategoryFullNames';

    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly HeurekaProductDataBatchLoader $productDataBatchLoader,
        protected readonly HeurekaCategoryFacade $heurekaCategoryFacade,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly InMemoryCache $inMemoryCache,
        protected readonly Setting $setting,
    ) {
    }

    public function create(Product $product, DomainConfig $domainConfig): HeurekaFeedItem
    {
        $mainVariantId = $product->isVariant() ? $product->getMainVariant()->getId() : null;

        return new HeurekaFeedItem(
            $product->getId(),
            $product->getFullName($domainConfig->getLocale()),
            $this->productDataBatchLoader->getProductParametersByName($product, $domainConfig),
            $this->productDataBatchLoader->getProductUrl($product, $domainConfig),
            $this->getPrice($product, $domainConfig),
            $mainVariantId,
            $product->getDescriptionAsPlainText($domainConfig->getId()),
            $this->productDataBatchLoader->getProductImageUrl($product, $domainConfig),
            $this->getBrandName($product),
            $product->getEan(),
            $this->getProductAvailabilityDays($product, $domainConfig->getId()),
            $this->getHeurekaCategoryFullName($product, $domainConfig),
            $this->productDataBatchLoader->getProductCpc($product, $domainConfig),
            $this->getSpecialServices($product, $domainConfig),
        );
    }

    /**
     * @return string[]
     */
    protected function getSpecialServices(Product $product, DomainConfig $domainConfig): array
    {
        return $this->productDataBatchLoader->getProductAdditionalServiceSpecialServiceNames($product, $domainConfig);
    }

    protected function getBrandName(Product $product): ?string
    {
        $brand = $product->getBrand();

        return $brand !== null ? $brand->getName() : null;
    }

    protected function getPrice(Product $product, DomainConfig $domainConfig): PriceInterface
    {
        return $this->productPriceCalculationForCustomerUser->calculatePricesForCustomerUserAndDomainId(
            $product,
            $domainConfig->getId(),
        )->sellingProductPrice->getPrice();
    }

    protected function getHeurekaCategoryFullName(Product $product, DomainConfig $domainConfig): ?string
    {
        $mainCategory = $this->categoryFacade->findProductMainCategoryByDomainId($product, $domainConfig->getId());

        if ($mainCategory !== null) {
            return $this->findHeurekaCategoryFullNameByCategoryIdUsingCache($mainCategory->getId(), $domainConfig->getLocale());
        }

        return null;
    }

    protected function findHeurekaCategoryFullNameByCategoryIdUsingCache(int $categoryId, string $locale): ?string
    {
        $key = $categoryId . '_' . $locale;

        return $this->inMemoryCache->getOrSaveValue(
            static::HEUREKA_CATEGORY_FULL_NAMES_CACHE_NAMESPACE,
            fn () => $this->findHeurekaCategoryFullNameByCategoryId(
                $categoryId,
                $locale,
            ),
            $key,
        );
    }

    protected function findHeurekaCategoryFullNameByCategoryId(int $categoryId, string $locale): ?string
    {
        $heurekaCategory = $this->heurekaCategoryFacade->findByCategoryIdAndLocale($categoryId, $locale);

        return $heurekaCategory !== null ? $heurekaCategory->getFullName() : null;
    }

    protected function getProductAvailabilityDays(Product $product, int $domainId): ?int
    {
        if ($this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainId)) {
            return 0;
        }

        return $this->productAvailabilityFacade->findDaysUntilExpectedRestocking($product, $domainId)
            ?? $this->setting->getForDomain(HeurekaFeedSettingEnum::HEUREKA_FEED_DELIVERY_DAYS, $domainId);
    }
}
