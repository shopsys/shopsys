<?php

namespace Shopsys\ProductFeed\GoogleBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;

class GoogleFeedItemFactory
{
    protected ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser;

    protected CurrencyFacade $currencyFacade;

    protected ProductUrlsBatchLoader $productUrlsBatchLoader;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader $productUrlsBatchLoader
     */
    public function __construct(
        ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        CurrencyFacade $currencyFacade,
        ProductUrlsBatchLoader $productUrlsBatchLoader
    ) {
        $this->productPriceCalculationForCustomerUser = $productPriceCalculationForCustomerUser;
        $this->currencyFacade = $currencyFacade;
        $this->productUrlsBatchLoader = $productUrlsBatchLoader;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItem
     */
    public function create(Product $product, DomainConfig $domainConfig): GoogleFeedItem
    {
        return new GoogleFeedItem(
            $product->getId(),
            $product->getName($domainConfig->getLocale()),
            $this->getBrandName($product),
            $product->getDescription($domainConfig->getId()),
            $product->getEan(),
            $product->getPartno(),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            $this->productUrlsBatchLoader->getProductImageUrl($product, $domainConfig),
            $product->getCalculatedSellingDenied(),
            $this->getPrice($product, $domainConfig),
            $this->getCurrency($domainConfig)
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
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    protected function getCurrency(DomainConfig $domainConfig): Currency
    {
        return $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainConfig->getId());
    }
}
