<?php

namespace Shopsys\ProductFeed\GoogleBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser;
use Shopsys\FrameworkBundle\Model\Product\Product;

class GoogleFeedItemFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser
     */
    protected $productPriceCalculationForUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader
     */
    protected $productUrlsBatchLoader;

    public function __construct(
        ProductPriceCalculationForUser $productPriceCalculationForUser,
        CurrencyFacade $currencyFacade,
        ProductUrlsBatchLoader $productUrlsBatchLoader
    ) {
        $this->productPriceCalculationForUser = $productPriceCalculationForUser;
        $this->currencyFacade = $currencyFacade;
        $this->productUrlsBatchLoader = $productUrlsBatchLoader;
    }

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
            $product->isSellingDenied(),
            $this->getPrice($product, $domainConfig),
            $this->getCurrency($domainConfig)
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

    protected function getCurrency(DomainConfig $domainConfig): Currency
    {
        return $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainConfig->getId());
    }
}
