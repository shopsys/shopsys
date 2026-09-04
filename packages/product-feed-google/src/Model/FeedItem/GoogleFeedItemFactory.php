<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle\Model\FeedItem;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductAdditionalServicesBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;

class GoogleFeedItemFactory
{
    protected const string CUSTOM_LABEL_SEPARATOR = ';';

    protected const int CUSTOM_LABEL_MAX_LENGTH = 100;

    public function __construct(
        protected readonly ProductPriceCalculation $productPriceCalculation,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly SpecialPriceFacade $specialPriceFacade,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductAdditionalServicesBatchLoader $productAdditionalServicesBatchLoader,
    ) {
    }

    public function create(Product $product, DomainConfig $domainConfig): GoogleFeedItem
    {
        $basicPrice = $this->getBasicPrice($product, $domainConfig);
        $availabilityDate = $this->findAvailabilityDate($product, $domainConfig);

        return new GoogleFeedItem(
            $product->getId(),
            $product->getFullName($domainConfig->getLocale()),
            $this->getAvailability($product, $domainConfig, $availabilityDate),
            $basicPrice,
            $this->getSpecialPrice($basicPrice, $product, $domainConfig),
            $this->getCurrency($domainConfig),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            $this->getBrandName($product),
            $product->getDescriptionAsPlainText($domainConfig->getId()),
            $product->getEan(),
            $product->getPartno(),
            $this->productUrlsBatchLoader->getProductImageUrl($product, $domainConfig),
            $availabilityDate,
            $this->getCustomLabel0($product, $domainConfig),
        );
    }

    protected function getCustomLabel0(Product $product, DomainConfig $domainConfig): ?string
    {
        $additionalServiceFeedNames = $this->productAdditionalServicesBatchLoader->getShownInFeedsFeedNames(
            $product,
            $domainConfig,
        );

        if ($additionalServiceFeedNames === []) {
            return null;
        }

        $sanitizedFeedNames = str_replace(
            static::CUSTOM_LABEL_SEPARATOR,
            ',',
            $additionalServiceFeedNames,
        );

        return $this->buildCustomLabelFromWholeFeedNames($sanitizedFeedNames);
    }

    /**
     * @param string[] $sanitizedFeedNames
     */
    protected function buildCustomLabelFromWholeFeedNames(array $sanitizedFeedNames): ?string
    {
        $customLabel = '';

        foreach ($sanitizedFeedNames as $feedName) {
            $extendedCustomLabel = $customLabel === ''
                ? $feedName
                : $customLabel . static::CUSTOM_LABEL_SEPARATOR . $feedName;

            if (mb_strlen($extendedCustomLabel) > static::CUSTOM_LABEL_MAX_LENGTH) {
                continue;
            }

            $customLabel = $extendedCustomLabel;
        }

        return $customLabel === '' ? null : $customLabel;
    }

    protected function getAvailability(
        Product $product,
        DomainConfig $domainConfig,
        ?DateTimeImmutable $availabilityDate,
    ): string {
        if ($availabilityDate !== null) {
            return GoogleFeedItem::AVAILABILITY_BACKORDER;
        }

        return $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainConfig->getId())
            ? GoogleFeedItem::AVAILABILITY_IN_STOCK
            : GoogleFeedItem::AVAILABILITY_OUT_OF_STOCK;
    }

    protected function findAvailabilityDate(Product $product, DomainConfig $domainConfig): ?DateTimeImmutable
    {
        if (!$product->isAllowedNegativeStock()) {
            return null;
        }

        return $this->productAvailabilityFacade->findEffectiveExpectedRestockingDate($product, $domainConfig->getId());
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
        return $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainConfig->getId());
    }
}
