<?php

namespace Shopsys\ProductFeed\ZboziBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain;

class ZboziFeedItemFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader $productUrlsBatchLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader $productParametersBatchLoader
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly ProductParametersBatchLoader $productParametersBatchLoader,
        protected readonly CategoryFacade $categoryFacade
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain|null $zboziProductDomain
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItem
     */
    public function create(Product $product, ?ZboziProductDomain $zboziProductDomain, DomainConfig $domainConfig): ZboziFeedItem
    {
        $mainVariantId = $product->isVariant() ? $product->getMainVariant()->getId() : null;
        $cpc = $zboziProductDomain !== null ? $zboziProductDomain->getCpc() : null;
        $cpcSearch = $zboziProductDomain !== null ? $zboziProductDomain->getCpcSearch() : null;

        return new ZboziFeedItem(
            $product->getId(),
            $mainVariantId,
            $product->getName($domainConfig->getLocale()),
            $product->getDescription($domainConfig->getId()),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            $this->productUrlsBatchLoader->getProductImageUrl($product, $domainConfig),
            $this->getBrandName($product),
            $product->getEan(),
            $product->getPartno(),
            $product->getCalculatedAvailability()->getDispatchTime(),
            $this->getPrice($product, $domainConfig),
            $this->getPathToMainCategory($product, $domainConfig),
            $this->productParametersBatchLoader->getProductParametersByName($product, $domainConfig),
            $cpc,
            $cpcSearch
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    protected function getBrandName(Product $product): ?string
    {
        $brand = $product->getBrand();

        return $brand !== null ? $brand->getName() : null;
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
            null
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string[]
     */
    protected function getPathToMainCategory(Product $product, DomainConfig $domainConfig): array
    {
        return $this->categoryFacade->getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(
            $product,
            $domainConfig
        );
    }
}
