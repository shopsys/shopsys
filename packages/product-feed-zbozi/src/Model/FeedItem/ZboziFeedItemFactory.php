<?php

namespace Shopsys\ProductFeed\ZboziBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain;

class ZboziFeedItemFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser
     */
    protected $productPriceCalculationForUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader
     */
    protected $productUrlsBatchLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader
     */
    protected $productParametersBatchLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    protected $categoryFacade;

    public function __construct(
        ProductPriceCalculationForUser $productPriceCalculationForUser,
        ProductUrlsBatchLoader $productUrlsBatchLoader,
        ProductParametersBatchLoader $productParametersBatchLoader,
        CategoryFacade $categoryFacade
    ) {
        $this->productPriceCalculationForUser = $productPriceCalculationForUser;
        $this->productUrlsBatchLoader = $productUrlsBatchLoader;
        $this->productParametersBatchLoader = $productParametersBatchLoader;
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain|null $zboziProductDomain
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

    protected function getBrandName(Product $product): ?string
    {
        $brand = $product->getBrand();

        return $brand !== null ? $brand->getName() : null;
    }

    protected function getPrice(Product $product, DomainConfig $domainConfig): Price
    {
        return $this->productPriceCalculationForUser->calculatePriceForUserAndDomainId(
            $product,
            $domainConfig->getId(),
            null
        );
    }

    /**
     * @return string[]
     */
    protected function getPathToMainCategory(Product $product, DomainConfig $domainConfig): array
    {
        return $this->categoryFacade->getCategoryNamesInPathFromRootToProductMainCategoryOnDomain($product, $domainConfig);
    }
}
