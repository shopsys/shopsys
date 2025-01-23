<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\Setting\LuigisBoxFeedSettingEnum;

class LuigisBoxProductFeedItemFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader $productUrlsBatchLoader
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly Setting $setting,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem
     */
    public function create(Product $product, DomainConfig $domainConfig): LuigisBoxProductFeedItem
    {
        $locale = $domainConfig->getLocale();
        $rootCategory = $this->categoryRepository->getRootCategory();
        $mainCategory = $this->categoryRepository->getProductMainCategoryOnDomain($product, $domainConfig->getId());
        $availabilityText = $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $domainConfig->getId());
        $productDescription = $product->isVariant() ? $product->getMainVariant()->getDescriptionAsPlainText($domainConfig->getId()) : $product->getDescriptionAsPlainText($domainConfig->getId());
        $categories = $product->getCategoriesIndexedByDomainId()[$domainConfig->getId()];
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

        $parameterValuesIndexedByName = [];

        foreach ($this->productCachedAttributesFacade->getProductParameterValues($product, $locale) as $productParameterValue) {
            $parameterName = str_replace('.', '', $productParameterValue->getParameter()->getName($locale));
            $parameterValue = $productParameterValue->getParameter()->isSlider() ? $productParameterValue->getValue()->getNumericValue() : $productParameterValue->getValue()->getText();

            if ($parameterValue !== null) {
                $parameterValuesIndexedByName[$parameterName] = $parameterValue;
            }
        }

        $mainVariantId = null;

        if ($product->isMainVariant()) {
            $mainVariantId = $product->getId();
        } elseif ($product->isVariant()) {
            $mainVariantId = $product->getMainVariant()->getId();
        }

        return new LuigisBoxProductFeedItem(
            $product->getId(),
            $product->getFullName($domainConfig->getLocale()),
            $product->getCatnum(),
            $availabilityText,
            $this->getAvailabilityRank($product, $domainConfig),
            $this->getPrice($product, $domainConfig),
            $this->getBasicPrice($product, $domainConfig),
            $this->getCurrency($domainConfig),
            $mainCategory->getId(),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            array_reverse($categoryHierarchyNamesByCategoryId, true),
            $product->isMainVariant(),
            array_map(fn (Flag $flag): string => $flag->getName($locale), $product->getFlags($domainConfig->getId())),
            $parameterValuesIndexedByName,
            $mainCategory->getName($locale),
            $product->getEan(),
            $product->getCatnum(),
            $product->getBrand()?->getName(),
            $productDescription,
            $this->productUrlsBatchLoader->getProductImageUrl($product, $domainConfig),
            $mainVariantId,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    protected function getPrice(Product $product, DomainConfig $domainConfig): PriceInterface
    {
        return $this->productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId(
            $product,
            $domainConfig->getId(),
        )->getPrice();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    protected function getBasicPrice(Product $product, DomainConfig $domainConfig): PriceInterface
    {
        return $this->productPriceCalculationForCustomerUser->calculateBasicPriceForCustomerUserAndDomainId(
            $product,
            $domainConfig->getId(),
        )->getPrice();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    protected function getCurrency(DomainConfig $domainConfig): Currency
    {
        return $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainConfig->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return int
     */
    protected function getAvailabilityRank(Product $product, DomainConfig $domainConfig): int
    {
        return $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainConfig->getId()) ? 1 : $this->setting->getForDomain(LuigisBoxFeedSettingEnum::LUIGIS_BOX_RANK, $domainConfig->getId());
    }
}
