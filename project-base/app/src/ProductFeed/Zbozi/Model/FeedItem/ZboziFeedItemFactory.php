<?php

declare(strict_types=1);

namespace App\ProductFeed\Zbozi\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItem;
use Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItemFactory as BaseZboziFeedItemFactory;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain;

/**
 * @method string|null getBrandName(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price getPrice(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method string[] getPathToMainCategory(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @method __construct(\Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser, \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader $productUrlsBatchLoader, \Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader $productParametersBatchLoader, \App\Model\Category\CategoryFacade $categoryFacade, \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade)
 */
class ZboziFeedItemFactory extends BaseZboziFeedItemFactory
{
    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain|null $zboziProductDomain
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItem
     */
    public function create(
        Product $product,
        ?ZboziProductDomain $zboziProductDomain,
        DomainConfig $domainConfig,
    ): ZboziFeedItem {
        $mainVariantId = $product->isVariant() ? $product->getMainVariant()->getId() : null;
        $cpc = $zboziProductDomain !== null ? $zboziProductDomain->getCpc() : null;
        $cpcSearch = $zboziProductDomain !== null ? $zboziProductDomain->getCpcSearch() : null;

        return new ZboziFeedItem(
            $product->getId(),
            $product->getFullname($domainConfig->getLocale()),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            $this->getPrice($product, $domainConfig),
            $this->getPathToMainCategory($product, $domainConfig),
            $this->productParametersBatchLoader->getProductParametersByName($product, $domainConfig),
            $mainVariantId,
            $product->getDescriptionAsPlainText($domainConfig->getId()),
            $this->productUrlsBatchLoader->getResizedProductImageUrl($product, $domainConfig),
            $this->getBrandName($product),
            $product->getEan(),
            $product->getPartno(),
            $this->productAvailabilityFacade->getProductAvailabilityDaysByDomainId($product, $domainConfig->getId()),
            $cpc,
            $cpcSearch,
        );
    }
}
