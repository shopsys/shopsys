<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain;

class ZboziFeedItemFactory
{
    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly ProductParametersBatchLoader $productParametersBatchLoader,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
    ) {
    }

    public function create(
        Product $product,
        ?ZboziProductDomain $zboziProductDomain,
        DomainConfig $domainConfig,
        ?string $zboziCategoryText = null,
    ): ZboziFeedItem {
        $mainVariantId = $product->isVariant() ? $product->getMainVariant()->getId() : null;
        $cpc = $zboziProductDomain?->getCpc();
        $cpcSearch = $zboziProductDomain?->getCpcSearch();

        return new ZboziFeedItem(
            $product->getId(),
            $product->getFullName($domainConfig->getLocale()),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            $this->getPrice($product, $domainConfig),
            $zboziCategoryText,
            $this->productParametersBatchLoader->getProductParametersByName($product, $domainConfig),
            $mainVariantId,
            $product->getDescriptionAsPlainText($domainConfig->getId()),
            $this->productUrlsBatchLoader->getProductImageUrl($product, $domainConfig),
            $product->getBrand()?->getName(),
            $product->getEan(),
            $product->getPartno(),
            $this->productAvailabilityFacade->getProductAvailabilityDaysForFeedsByDomainId($product, $domainConfig->getId()),
            $cpc,
            $cpcSearch,
        );
    }

    protected function getPrice(Product $product, DomainConfig $domainConfig): PriceInterface
    {
        return $this->productPriceCalculationForCustomerUser->calculatePricesForCustomerUserAndDomainId(
            $product,
            $domainConfig->getId(),
        )->sellingProductPrice->getPrice();
    }
}
