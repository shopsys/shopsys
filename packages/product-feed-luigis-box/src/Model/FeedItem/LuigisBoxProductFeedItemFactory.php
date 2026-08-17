<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\Setting\LuigisBoxFeedSettingEnum;

class LuigisBoxProductFeedItemFactory
{
    protected const int SMALL_IMAGE_SIZE = 100;
    protected const int MEDIUM_IMAGE_SIZE = 200;
    protected const int LARGE_IMAGE_SIZE = 600;

    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly Setting $setting,
        protected readonly ImageUrlWithSizeHelper $imageUrlWithSizeHelper,
        protected readonly PricingSetting $pricingSetting,
    ) {
    }

    public function create(Product $product, DomainConfig $domainConfig): LuigisBoxProductFeedItem
    {
        $locale = $domainConfig->getLocale();
        $domainId = $domainConfig->getId();

        $mainCategory = $this->categoryRepository->getProductMainCategoryOnDomain($product, $domainId);
        $availabilityText = $this->productAvailabilityFacade->getProductAvailabilityInfoByProduct($product, $domainId)->name;
        $productDescription = $product->isVariant() ? $product->getMainVariant()->getDescriptionAsPlainText($domainId) : $product->getDescriptionAsPlainText($domainId);

        $mainVariantId = null;

        if ($product->isMainVariant()) {
            $mainVariantId = $product->getId();
        } elseif ($product->isVariant()) {
            $mainVariantId = $product->getMainVariant()->getId();
        }

        $imageUrl = $this->productUrlsBatchLoader->getProductImageUrl($product, $domainConfig);

        $productPrices = $this->productPriceCalculationForCustomerUser->calculatePricesForCustomerUserAndDomainId(
            $product,
            $domainId,
        );

        return new LuigisBoxProductFeedItem(
            $product->getId(),
            $product->getFullName($domainConfig->getLocale()),
            $availabilityText,
            $this->getAvailabilityRank($product, $domainConfig),
            $this->getProperAmountUsingSellingPriceType($productPrices->sellingProductPrice),
            $this->getProperAmountUsingSellingPriceType($productPrices->basicProductPrice),
            $this->getCurrency($domainConfig),
            $mainCategory->getId(),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            $this->getCategoryHierarchyNamesByCategoryId($product, $domainConfig),
            $product->isMainVariant(),
            array_map(static fn (Flag $flag): string => $flag->getName($locale), $product->getFlags($domainId)),
            $this->getParameterValuesIndexedByName($product, $locale),
            $product->getEan(),
            $product->getCatnum(),
            $product->getBrand()?->getName(),
            $productDescription,
            $mainVariantId,
            $imageUrl !== null ? $this->imageUrlWithSizeHelper->limitSizeInImageUrl($imageUrl, self::SMALL_IMAGE_SIZE, self::SMALL_IMAGE_SIZE) : null,
            $imageUrl !== null ? $this->imageUrlWithSizeHelper->limitSizeInImageUrl($imageUrl, self::MEDIUM_IMAGE_SIZE, self::MEDIUM_IMAGE_SIZE) : null,
            $imageUrl !== null ? $this->imageUrlWithSizeHelper->limitSizeInImageUrl($imageUrl, self::LARGE_IMAGE_SIZE, self::LARGE_IMAGE_SIZE) : null,
            $product->getSeoTitle($domainId),
            $product->getSeoMetaDescription($domainId),
            $product->getSeoH1($domainId),
        );
    }

    protected function getProperAmountUsingSellingPriceType(ProductPriceInterface $productPrice): Money
    {
        if ($this->pricingSetting->getSellingPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            return $productPrice->getPrice()->getPriceWithVat();
        }

        return $productPrice->getPrice()->getPriceWithoutVat();
    }

    protected function getCurrency(DomainConfig $domainConfig): Currency
    {
        return $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainConfig->getId());
    }

    protected function getAvailabilityRank(Product $product, DomainConfig $domainConfig): int
    {
        return $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainConfig->getId()) ? 1 : $this->setting->getForDomain(LuigisBoxFeedSettingEnum::LUIGIS_BOX_RANK, $domainConfig->getId());
    }

    /**
     * @return array<int, string>
     */
    protected function getCategoryHierarchyNamesByCategoryId(Product $product, DomainConfig $domainConfig): array
    {
        $categories = $product->getCategoriesIndexedByDomainId()[$domainConfig->getId()];
        $rootCategory = $this->categoryRepository->getRootCategory();
        $locale = $domainConfig->getLocale();

        $categoryHierarchyNamesByCategoryId = [];

        foreach ($categories as $category) {
            $categoryHierarchyNames = [];
            $parent = $category->getParent();
            $categoryHierarchyNames[] = $category->getName($locale);

            while ($parent !== null && $parent->getId() !== $rootCategory->getId()) {
                $categoryHierarchyNames[] = $parent->getName($locale);
                $parent = $parent->getParent();
            }

            $categoryHierarchyNamesByCategoryId[$category->getId()] = implode(' | ', array_reverse($categoryHierarchyNames));
        }

        return array_reverse($categoryHierarchyNamesByCategoryId, true);
    }

    /**
     * @return array<string, string>
     */
    protected function getParameterValuesIndexedByName(Product $product, string $locale): array
    {
        $parameterValuesIndexedByName = [];

        foreach ($this->productCachedAttributesFacade->getProductParameterValues($product, $locale) as $productParameterValue) {
            $parameterName = str_replace('.', '', $productParameterValue->getParameter()->getName($locale));
            $parameterValue = $productParameterValue->getParameter()->isSlider() ? $productParameterValue->getValue()->getNumericValue() : $productParameterValue->getValue()->getText();

            if ($parameterValue !== null) {
                $parameterValuesIndexedByName[$parameterName] = $parameterValue;
            }
        }

        return $parameterValuesIndexedByName;
    }
}
