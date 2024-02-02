<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;

class LuigisBoxFeedItemFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader $productUrlsBatchLoader
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     */
    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxFeedItem
     */
    public function create(Product $product, DomainConfig $domainConfig): LuigisBoxFeedItem
    {
        $locale = $domainConfig->getLocale();
        $rootCategory = $this->categoryRepository->getRootCategory();
        $mainCategory = $this->categoryRepository->getProductMainCategoryOnDomain($product, $domainConfig->getId());
        $availability = $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $domainConfig->getId());
        $productDescription = $product->isVariant() ? $product->getMainVariant()->getDescriptionAsPlainText($domainConfig->getId()) : $product->getDescriptionAsPlainText($domainConfig->getId());
        $categories = $product->getCategoriesIndexedByDomainId()[$domainConfig->getId()];
        $categoryHierarchyNamesByCategoryId = [];
        $categoryHierarchyIdsByCategoryId = [];

        foreach ($categories as $category) {
            $categoryHierarchyNames = [];
            $categoryHierarchyIds = [];
            $parent = $category->getParent();
            $categoryHierarchyNames[] = $category->getName($locale);
            $categoryHierarchyIds[] = $category->getId();

            while ($parent !== null && $parent->getId() !== $rootCategory->getId()) {
                $categoryHierarchyIds[] = $parent->getId();
                $categoryHierarchyNames[] = $parent->getName($locale);
                $parent = $parent->getParent();
            }

            $categoryHierarchyNamesByCategoryId[$category->getId()] = implode(' > ', array_reverse($categoryHierarchyNames));
            $categoryHierarchyIdsByCategoryId[$category->getId()] = implode(':', array_reverse($categoryHierarchyIds));
        }

        $parameterValuesIndexedByName = [];

        foreach ($this->productCachedAttributesFacade->getProductParameterValues($product, $locale) as $productParameterValue) {
            $parameterValuesIndexedByName[$productParameterValue->getParameter()->getName($locale)] = $productParameterValue->getValue()->getText();
        }

        return new LuigisBoxFeedItem(
            $product->getId(),
            $product->getName($domainConfig->getLocale()),
            $product->getCatnum(),
            $availability,
            $this->getPrice($product, $domainConfig),
            $this->getCurrency($domainConfig),
            $mainCategory->getId(),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            array_reverse($categoryHierarchyNamesByCategoryId),
            array_reverse($categoryHierarchyIdsByCategoryId),
            $product->isMainVariant(),
            array_map(fn (Flag $flag): string => TransformString::safeFilename($flag->getName($locale)), $product->getFlags($domainConfig->getId())),
            $parameterValuesIndexedByName,
            $mainCategory->getName($locale),
            $product->getEan(),
            $product->getPartno(),
            $product->getBrand()?->getName(),
            $productDescription,
            $this->productUrlsBatchLoader->getResizedProductImageUrl($product, $domainConfig),
            $product->isVariant() ? $product->getMainVariant()->getId() : null,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function getPrice(Product $product, DomainConfig $domainConfig): Price
    {
        return $this->productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId(
            $product,
            $domainConfig->getId(),
            null,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    protected function getCurrency(DomainConfig $domainConfig): Currency
    {
        return $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainConfig->getId());
    }
}
