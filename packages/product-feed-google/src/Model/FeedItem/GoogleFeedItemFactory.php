<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;

class GoogleFeedItemFactory
{
    public function __construct(
        protected readonly ProductPriceCalculation $productPriceCalculation,
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly SpecialPriceFacade $specialPriceFacade,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
    ) {
    }

    public function create(Product $product, DomainConfig $domainConfig): GoogleFeedItem
    {
        $basicPrice = $this->getBasicPrice($product, $domainConfig);

        return new GoogleFeedItem(
            $product->getId(),
            $product->getFullName($domainConfig->getLocale()),
            !$this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainConfig->getId()),
            $basicPrice,
            $this->getSpecialPrice($basicPrice, $product, $domainConfig),
            $this->getCurrency($domainConfig),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            $this->getBrandName($product),
            $product->getDescriptionAsPlainText($domainConfig->getId()),
            $product->getEan(),
            $product->getPartno(),
            $this->productUrlsBatchLoader->getProductImageUrl($product, $domainConfig),
        );
    }

    protected function getBrandName(Product $product): ?string
    {
        return $product->getBrand()?->getName();
    }

    protected function getBasicPrice(Product $product, DomainConfig $domainConfig): PriceInterface
    {
        $defaultPricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());

        return $this->productPriceCalculation->calculatePrice(
            $product,
            $domainConfig->getId(),
            $defaultPricingGroup,
        )->getPrice();
    }

    protected function getSpecialPrice(
        PriceInterface $basicPrice,
        Product $product,
        DomainConfig $domainConfig,
    ): ?SpecialPrice {
        $specialPrice = $this->specialPriceFacade->findRelevantSpecialPrice($product, $domainConfig->getId(), $basicPrice);

        if ($specialPrice === null || $specialPrice->isFuturePrice()) {
            return null;
        }

        return $specialPrice;
    }

    protected function getCurrency(DomainConfig $domainConfig): Currency
    {
        return $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($domainConfig->getId());
    }
}
