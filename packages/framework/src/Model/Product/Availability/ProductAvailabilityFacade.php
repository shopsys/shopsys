<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use DateTimeImmutable;
use IntlDateFormatter;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatterInterface;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductSellableVariantsProvider;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;

class ProductAvailabilityFacade
{
    protected const string PRODUCT_AVAILABILITY_CACHE_NAMESPACE = 'productAvailabilityDomain';
    protected const string STOCK_QUANTITIES_CACHE_NAMESPACE = 'productAvailabilityStockQuantities';

    /**
     * A deliberate product decision: up to this many days the feeds present the dispatch time
     * as a number of days, a restocking expected further away is expressed as the concrete date,
     * which tells the customer more (the Zbozi.cz DELIVERY_DATE element accepts both forms)
     *
     * @see https://napoveda.sklik.cz/reklamy/xml-feed/specifikace/#DELIVERY_DATE
     */
    protected const int MAX_DISPATCH_DAYS_FOR_NUMERIC_VALUE = 8;

    public function __construct(
        protected readonly Setting $setting,
        protected readonly ProductStockFacade $productStockFacade,
        protected readonly StoreFacade $storeFacade,
        protected readonly Domain $domain,
        protected readonly InMemoryCache $inMemoryCache,
        protected readonly ClockInterface $clock,
        protected readonly DateTimeFormatterInterface $dateTimeFormatter,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly ProductSellableVariantsProvider $productSellableVariantsProvider,
    ) {
    }

    public function getProductAvailabilityInfoByProduct(Product $product, int $domainId): ProductAvailabilityInfo
    {
        return $this->createProductAvailabilityInfo(
            $this->isProductAvailableOnDomainCached($product, $domainId),
            $this->findValidExpectedRestockingDate($product, $domainId),
            $domainId,
        );
    }

    public function createProductAvailabilityInfo(
        bool $isProductAvailable,
        ?DateTimeImmutable $expectedRestockingDate,
        int $domainId,
    ): ProductAvailabilityInfo {
        $domainLocale = $this->domain->getDomainConfigById($domainId)->getLocale();

        if ($expectedRestockingDate !== null
            && $this->hasExpectedRestockingDatePassed($expectedRestockingDate, $domainId)
        ) {
            $expectedRestockingDate = null;
        }

        if ($isProductAvailable) {
            return $this->createProductAvailabilityInfoInstance(
                $this->getOnStockText($domainLocale),
                AvailabilityStatusEnum::IN_STOCK,
            );
        }

        if ($expectedRestockingDate !== null) {
            return $this->createProductAvailabilityInfoInstance(
                $this->getExpectedRestockText($expectedRestockingDate, $domainId),
                AvailabilityStatusEnum::EXPECTED_RESTOCK,
            );
        }

        return $this->createProductAvailabilityInfoInstance(
            $this->getOutOfStockText($domainLocale),
            AvailabilityStatusEnum::OUT_OF_STOCK,
        );
    }

    protected function createProductAvailabilityInfoInstance(string $name, string $status): ProductAvailabilityInfo
    {
        return new ProductAvailabilityInfo($name, $status);
    }

    protected function getProductAvailabilityDaysForFeedsByDomainId(Product $product, int $domainId): int
    {
        if ($this->isProductAvailableOnDomainCached($product, $domainId)) {
            return 0;
        }

        return $this->findDaysUntilExpectedRestocking($product, $domainId)
            ?? $this->setting->getForDomain(Setting::FEED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS, $domainId);
    }

    /**
     * Intended for the Zbozi and Mergado feeds only. Returns the number of days until dispatch, or,
     * when a real expected restocking date exceeds the limit for a numeric value, the concrete
     * restocking date as a 'Y-m-d' string in the domain display timezone.
     */
    public function getProductAvailabilityDaysOrDateForFeedsByDomainId(Product $product, int $domainId): int|string
    {
        $dispatchDays = $this->getProductAvailabilityDaysForFeedsByDomainId($product, $domainId);

        if ($dispatchDays <= static::MAX_DISPATCH_DAYS_FOR_NUMERIC_VALUE) {
            return $dispatchDays;
        }

        $dispatchDate = $this->findEffectiveExpectedRestockingDate($product, $domainId);

        // the settings fallback for products without a restocking date stays numeric — a synthetic
        // date would shift with every feed run and would falsely suggest a known restocking
        if ($dispatchDate === null) {
            return $dispatchDays;
        }

        return $dispatchDate
            ->setTimezone($this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId))
            ->format('Y-m-d');
    }

    public function findDaysUntilExpectedRestocking(Product $product, int $domainId): ?int
    {
        $expectedRestockingDate = $this->findEffectiveExpectedRestockingDate($product, $domainId);

        if ($expectedRestockingDate === null) {
            return null;
        }

        return $this->getDaysUntilExpectedRestockingDate($expectedRestockingDate, $domainId);
    }

    protected function getDaysUntilExpectedRestockingDate(
        DateTimeImmutable $expectedRestockingDate,
        int $domainId,
    ): int {
        $displayTimeZone = $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId);
        $today = $this->clock->now()->setTimezone($displayTimeZone)->modify('midnight');
        $expectedRestockingDay = $expectedRestockingDate->setTimezone($displayTimeZone)->modify('midnight');

        return (int)$today->diff($expectedRestockingDay)->days;
    }

    public function getAvailableStoresCount(Product $product, int $domainId): ?int
    {
        if ($product->isMainVariant()) {
            return null;
        }

        $productStocks = $this->productStockFacade->getProductStocksByProduct($product);
        $storeCountsByStockId = $this->storeFacade->getStoreCountsByDomainIdIndexedByStockId($domainId);

        $count = 0;

        foreach ($productStocks as $productStock) {
            $stock = $productStock->getStock();

            if ($productStock->getProductQuantity() > 0 && $productStock->getStock()->isEnabled($domainId)) {
                $count += $storeCountsByStockId[$stock->getId()] ?? 0;
            }
        }

        return $count;
    }

    public function isProductAvailableOnDomainCached(Product $product, int $domainId): bool
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::PRODUCT_AVAILABILITY_CACHE_NAMESPACE,
            fn () => $this->isProductAvailableOnDomain($product, $domainId),
            $product->getId(),
            $domainId,
        );
    }

    protected function isProductAvailableOnDomain(Product $product, int $domainId): bool
    {
        if (!$product->isMainVariant()) {
            return $this->productStockFacade->isProductAvailableOnDomain($product, $domainId);
        }

        $variants = $this->productSellableVariantsProvider->getVariantsForDefaultPricingGroup($product, $domainId);
        $stockQuantitiesIndexedByProductId = $this->productStockFacade
            ->getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId($variants, $domainId);

        return array_any(
            $stockQuantitiesIndexedByProductId,
            static fn (int $stockQuantity): bool => $stockQuantity > 0,
        );
    }

    public function getTransferDaysByDomainId(int $domainId): int
    {
        return $this->setting->getForDomain(Setting::TRANSFER_DAYS_BETWEEN_STOCKS, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     */
    public function isTransferToStoreNeeded(array $quantifiedProducts, Store $store, int $domainId): bool
    {
        $stock = $store->getStock();

        if ($stock === null || !$stock->isEnabled($domainId)) {
            return true;
        }

        $stockQuantitiesIndexedByProductIdAndStockId = $this->getStockQuantitiesIndexedByProductIdAndStockIdCached(
            $quantifiedProducts,
        );

        foreach ($quantifiedProducts as $quantifiedProduct) {
            $stockQuantity = $stockQuantitiesIndexedByProductIdAndStockId[$quantifiedProduct->getProduct()->getId()][$stock->getId()] ?? 0;

            if ($quantifiedProduct->getQuantity() > $stockQuantity) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return array<int, array<int, int>> stock quantities indexed by product id and stock id
     */
    protected function getStockQuantitiesIndexedByProductIdAndStockIdCached(array $quantifiedProducts): array
    {
        $products = array_map(
            static fn (QuantifiedProduct $quantifiedProduct): Product => $quantifiedProduct->getProduct(),
            $quantifiedProducts,
        );
        $productIds = array_map(static fn (Product $product): int => $product->getId(), $products);
        sort($productIds);

        return $this->inMemoryCache->getOrSaveValue(
            static::STOCK_QUANTITIES_CACHE_NAMESPACE,
            fn (): array => $this->productStockFacade->getStockQuantitiesByProductsIndexedByProductIdAndStockId($products),
            implode(',', array_unique($productIds)),
        );
    }

    public function getGroupedStockQuantityByProductAndDomainId(Product $product, int $domainId): ?int
    {
        if ($product->isMainVariant()) {
            return null;
        }

        $productStocksByDomainIdIndexedByStockId = $this->productStockFacade->getProductStocksByProductAndDomainIdIndexedByStockId($product, $domainId);

        return $this->sumProductStockQuantities($productStocksByDomainIdIndexedByStockId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return array<int, int>
     */
    public function getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId(
        array $products,
        int $domainId,
    ): array {
        return $this->productStockFacade->getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId(
            $products,
            $domainId,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStock[] $productStocksByDomainIdIndexedByStockId
     */
    protected function sumProductStockQuantities(array $productStocksByDomainIdIndexedByStockId): int
    {
        $totalProductStocksQuantity = 0;

        foreach ($productStocksByDomainIdIndexedByStockId as $productStock) {
            $totalProductStocksQuantity += $productStock->getProductQuantity();
        }

        return $totalProductStocksQuantity;
    }

    public function getOnStockText(string $domainLocale): string
    {
        return t('In stock', [], Translator::CUSTOMER_TRANSLATION_DOMAIN, $domainLocale);
    }

    public function getOutOfStockText(string $domainLocale): string
    {
        return t('Out of stock', [], Translator::CUSTOMER_TRANSLATION_DOMAIN, $domainLocale);
    }

    protected function getExpectedRestockText(DateTimeImmutable $expectedRestockingDate, int $domainId): string
    {
        $domainLocale = $this->domain->getDomainConfigById($domainId)->getLocale();
        $displayTimeZone = $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId);
        $formattedDate = (string)$this->dateTimeFormatter->format(
            $expectedRestockingDate->setTimezone($displayTimeZone),
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE,
            $domainLocale,
        );

        return t('Expecting %date%', ['%date%' => $formattedDate], Translator::CUSTOMER_TRANSLATION_DOMAIN, $domainLocale);
    }

    /**
     * Returns the expected restocking date only when it is currently in effect,
     * i.e. the product is out of stock on the domain and the date is still valid.
     */
    public function findEffectiveExpectedRestockingDate(Product $product, int $domainId): ?DateTimeImmutable
    {
        if ($this->isProductAvailableOnDomainCached($product, $domainId)) {
            return null;
        }

        return $this->findValidExpectedRestockingDate($product, $domainId);
    }

    /**
     * Returns the expected restocking date whenever it is filled and has not passed yet,
     * regardless of the stock availability (partially stocked cart items need the date too).
     * The date is considered valid throughout the whole restocking day in the domain display timezone.
     */
    public function findValidExpectedRestockingDate(Product $product, int $domainId): ?DateTimeImmutable
    {
        if ($product->isMainVariant()) {
            return $this->findEarliestValidExpectedRestockingDateOfVariants($product, $domainId);
        }

        $expectedRestockingDate = $product->getExpectedRestockingDate();

        if ($expectedRestockingDate === null
            || $this->hasExpectedRestockingDatePassed($expectedRestockingDate, $domainId)
        ) {
            return null;
        }

        return $expectedRestockingDate;
    }

    protected function findEarliestValidExpectedRestockingDateOfVariants(
        Product $mainVariant,
        int $domainId,
    ): ?DateTimeImmutable {
        $earliestExpectedRestockingDate = null;

        $variants = $this->productSellableVariantsProvider->getVariantsForDefaultPricingGroup($mainVariant, $domainId);

        foreach ($variants as $variant) {
            $expectedRestockingDate = $this->findValidExpectedRestockingDate($variant, $domainId);

            if ($expectedRestockingDate !== null
                && ($earliestExpectedRestockingDate === null || $expectedRestockingDate < $earliestExpectedRestockingDate)
            ) {
                $earliestExpectedRestockingDate = $expectedRestockingDate;
            }
        }

        return $earliestExpectedRestockingDate;
    }

    public function hasExpectedRestockingDatePassed(
        DateTimeImmutable $expectedRestockingDate,
        int $domainId,
    ): bool {
        $displayTimeZone = $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId);
        $firstMomentAfterRestockingDay = $expectedRestockingDate->setTimezone($displayTimeZone)->modify('tomorrow midnight');

        return $this->clock->now() >= $firstMomentAfterRestockingDay;
    }

    public function getNotOnStockQuantity(Product $product, int $domainId, int $quantityToAdd): ?int
    {
        if ($product->isMainVariant()) {
            return null;
        }

        $notOnStockQuantity = 0;
        $productTotalQuantity = $this->getGroupedStockQuantityByProductAndDomainId($product, $domainId);

        if ($quantityToAdd > $productTotalQuantity) {
            $notOnStockQuantity = $quantityToAdd - $productTotalQuantity;
        }

        return $notOnStockQuantity;
    }
}
