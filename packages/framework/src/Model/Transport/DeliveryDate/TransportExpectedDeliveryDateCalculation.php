<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\DeliveryDate;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServicesDeliveryDaysExtensionCalculation;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\StoreOpeningHoursProvider;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\Exception\TransportIsNotPersonalPickupException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class TransportExpectedDeliveryDateCalculation
{
    /**
     * A safety bound for the delivery date postponing so that a pathological closed days configuration
     * cannot cause an endless loop; a delivery date is expected to be found within a few days — when
     * no allowed day exists within the bound, no delivery date is promised at all
     */
    protected const int MAX_POSTPONE_DAYS = 366;

    protected const string DATE_INDEX_FORMAT = 'Y-m-d';
    protected const string DISPATCH_DATE_CACHE_NAMESPACE = 'transportExpectedDeliveryDateDispatchDate';
    protected const string CLOSED_DAYS_CACHE_NAMESPACE = 'transportExpectedDeliveryDateClosedDays';
    protected const string STORES_CACHE_NAMESPACE = 'transportExpectedDeliveryDateStores';

    public function __construct(
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly ClockInterface $clock,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly ClosedDayFacade $closedDayFacade,
        protected readonly StoreFacade $storeFacade,
        protected readonly InMemoryCache $inMemoryCache,
        protected readonly StoreOpeningHoursProvider $storeOpeningHoursProvider,
        protected readonly AdditionalServicesDeliveryDaysExtensionCalculation $additionalServicesDeliveryDaysExtensionCalculation,
    ) {
    }

    /**
     * Returns the expected delivery date of an order placed today; null when no date can be promised
     */
    public function calculateExpectedDeliveryDate(
        Transport $transport,
        ?Cart $cart,
        int $domainId,
    ): ?DateTimeImmutable {
        return $this->calculateExpectedDeliveryDateForQuantifiedProducts(
            $transport,
            $cart?->getQuantifiedProducts() ?? [],
            $domainId,
            $this->findPickupPlaceIdentifierSelectedInCartForTransport($transport, $cart),
        );
    }

    /**
     * Returns the expected delivery date of an order of the given products placed today; null when no date can be promised
     *
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     */
    public function calculateExpectedDeliveryDateForQuantifiedProducts(
        Transport $transport,
        array $quantifiedProducts,
        int $domainId,
        ?string $pickupPlaceIdentifier = null,
    ): ?DateTimeImmutable {
        $quantifiedProducts = array_values($quantifiedProducts);

        return $this->calculateDeliveryDate(
            $transport,
            $quantifiedProducts,
            $domainId,
            $this->findSelectedStoreForTransport($transport, $pickupPlaceIdentifier, $domainId),
            $this->additionalServicesDeliveryDaysExtensionCalculation->calculateHighestDeliveryDaysExtension($quantifiedProducts),
        );
    }

    /**
     * The store selected in the cart is deliberately ignored — the store picker offers every store independently
     */
    public function calculateExpectedDeliveryDateForStore(
        Transport $transport,
        ?Cart $cart,
        int $domainId,
        Store $store,
    ): ?DateTimeImmutable {
        $this->assertPersonalPickupTransport($transport);
        $quantifiedProducts = array_values($cart?->getQuantifiedProducts() ?? []);

        return $this->calculateDeliveryDate(
            $transport,
            $quantifiedProducts,
            $domainId,
            $store,
            $this->additionalServicesDeliveryDaysExtensionCalculation->calculateHighestDeliveryDaysExtension($quantifiedProducts),
        );
    }

    /**
     * Returns the expected delivery date of a single piece of the product ordered today, independently of any cart
     */
    public function calculateExpectedDeliveryDateForProduct(
        Transport $transport,
        Product $product,
        int $domainId,
    ): ?DateTimeImmutable {
        return $this->calculateDeliveryDate($transport, [new QuantifiedProduct($product, 1)], $domainId, null);
    }

    public function calculateExpectedDeliveryDateForStoreAndProduct(
        Transport $transport,
        Product $product,
        int $domainId,
        Store $store,
    ): ?DateTimeImmutable {
        $this->assertPersonalPickupTransport($transport);

        return $this->calculateDeliveryDate($transport, [new QuantifiedProduct($product, 1)], $domainId, $store);
    }

    protected function assertPersonalPickupTransport(Transport $transport): void
    {
        if (!$transport->isPersonalPickup()) {
            throw new TransportIsNotPersonalPickupException(
                'The expected delivery date for a store can only be calculated for a personal pickup transport.',
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     */
    protected function calculateDeliveryDate(
        Transport $transport,
        array $quantifiedProducts,
        int $domainId,
        ?Store $store,
        int $deliveryDaysExtension = 0,
    ): ?DateTimeImmutable {
        if ($store === null && $transport->isPersonalPickup()) {
            return $this->calculateBestPickupDeliveryDateAcrossStores(
                $transport,
                $quantifiedProducts,
                $domainId,
                $deliveryDaysExtension,
            );
        }

        // the dispatch date does not depend on the transport nor the store, so one set of products resolves it just
        // once per request even when the date is being calculated for a whole transport listing or store picker
        $dispatchDate = $this->inMemoryCache->getOrSaveValue(
            static::DISPATCH_DATE_CACHE_NAMESPACE,
            fn (): ?DateTimeImmutable => $this->findDispatchDate($quantifiedProducts, $domainId),
            $this->getQuantifiedProductsCacheKey($quantifiedProducts),
            $domainId,
        );

        if ($dispatchDate === null) {
            return null;
        }

        $dispatchDate = $this->postponeDispatchDateByTransferDaysIfNeeded($dispatchDate, $quantifiedProducts, $domainId, $store);

        $closestPossibleDeliveryDate = $dispatchDate->modify(sprintf('+%d days', $transport->getDaysUntilDelivery()));

        $deliveryDate = $this->postponeToFirstAllowedDeliveryDay(
            $transport,
            $closestPossibleDeliveryDate,
            $domainId,
            $store,
            $deliveryDaysExtension,
        );

        return $deliveryDate?->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     */
    protected function calculateBestPickupDeliveryDateAcrossStores(
        Transport $transport,
        array $quantifiedProducts,
        int $domainId,
        int $deliveryDaysExtension,
    ): ?DateTimeImmutable {
        $bestDeliveryDate = null;

        foreach ($this->getStoresByDomainIdCached($domainId) as $store) {
            $deliveryDate = $this->calculateDeliveryDate(
                $transport,
                $quantifiedProducts,
                $domainId,
                $store,
                $deliveryDaysExtension,
            );

            if ($deliveryDate !== null && ($bestDeliveryDate === null || $deliveryDate < $bestDeliveryDate)) {
                $bestDeliveryDate = $deliveryDate;
            }
        }

        return $bestDeliveryDate;
    }

    /**
     * The dispatch date is derived from the (product, quantity) pairs, so their fingerprint is the cache key —
     * a mutation modifying the cart and resolving the delivery date within the same request gets a fresh value;
     * the stocks are considered stable within a request
     *
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     */
    protected function getQuantifiedProductsCacheKey(array $quantifiedProducts): string
    {
        if ($quantifiedProducts === []) {
            return 'none';
        }

        $quantifiedProductParts = array_map(
            static fn (QuantifiedProduct $quantifiedProduct): string => $quantifiedProduct->getProduct()->getId() . ':' . $quantifiedProduct->getQuantity(),
            $quantifiedProducts,
        );

        return implode(',', $quantifiedProductParts);
    }

    protected function findPickupPlaceIdentifierSelectedInCartForTransport(Transport $transport, ?Cart $cart): ?string
    {
        if ($cart === null || $cart->getTransport()?->getId() !== $transport->getId()) {
            return null;
        }

        return $cart->getPickupPlaceIdentifier();
    }

    protected function findSelectedStoreForTransport(
        Transport $transport,
        ?string $pickupPlaceIdentifier,
        int $domainId,
    ): ?Store {
        if ($pickupPlaceIdentifier === null || !$transport->isPersonalPickup()) {
            return null;
        }

        return $this->storeFacade->findByUuidAndDomainId($pickupPlaceIdentifier, $domainId);
    }

    /**
     * Returns the day the order can be dispatched — today, or the worst expected restocking date
     * of the awaited products; null when an awaited product has no valid restocking date
     *
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     */
    protected function findDispatchDate(array $quantifiedProducts, int $domainId): ?DateTimeImmutable
    {
        $awaitedQuantifiedProducts = $this->getQuantifiedProductsAwaitingRestocking($quantifiedProducts, $domainId);

        if ($awaitedQuantifiedProducts === []) {
            return $this->getToday($domainId);
        }

        $worstExpectedRestockingDate = $this->findWorstExpectedRestockingDate($awaitedQuantifiedProducts, $domainId);

        if ($worstExpectedRestockingDate === null) {
            return null;
        }

        return $this->getStartOfDay($worstExpectedRestockingDate, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     */
    protected function postponeDispatchDateByTransferDaysIfNeeded(
        DateTimeImmutable $dispatchDate,
        array $quantifiedProducts,
        int $domainId,
        ?Store $store,
    ): DateTimeImmutable {
        if (
            $quantifiedProducts === []
            || !$this->isTransferNeeded($quantifiedProducts, $domainId, $store)
        ) {
            return $dispatchDate;
        }

        return $dispatchDate->modify(sprintf('+%d days', $this->productAvailabilityFacade->getTransferDaysByDomainId($domainId)));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     */
    protected function isTransferNeeded(array $quantifiedProducts, int $domainId, ?Store $store): bool
    {
        // a carrier transport dispatches from any stock, only a store may need a transfer
        return $store !== null
            && $this->productAvailabilityFacade->isTransferToStoreNeeded($quantifiedProducts, $store, $domainId);
    }

    protected function postponeToFirstAllowedDeliveryDay(
        Transport $transport,
        DateTimeImmutable $deliveryDate,
        int $domainId,
        ?Store $store,
        int $deliveryDaysExtension = 0,
    ): ?DateTimeImmutable {
        $closedDaysIndexedByDate = $this->getClosedDaysForPostponeWindowIndexedByDate($domainId, $deliveryDate);

        $remainingDeliveryDaysExtension = $deliveryDaysExtension;

        for ($postponedDays = 0; $postponedDays < static::MAX_POSTPONE_DAYS; $postponedDays++) {
            $isDeliveryAllowed = $this->isDeliveryAllowedOnDate(
                $transport,
                $deliveryDate,
                $domainId,
                $store,
                $closedDaysIndexedByDate[$deliveryDate->format(static::DATE_INDEX_FORMAT)] ?? [],
            );

            if ($isDeliveryAllowed) {
                if ($remainingDeliveryDaysExtension === 0) {
                    return $deliveryDate;
                }

                $remainingDeliveryDaysExtension--;
            }

            $deliveryDate = $deliveryDate->modify('+1 day');
        }

        return null;
    }

    /**
     * Fetches all the closed days the postponing may ever need in a single query; the window is cached
     * per request, so every transport and store sharing the first candidate date reuses the same result
     *
     * @return array<string, \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[]>
     */
    protected function getClosedDaysForPostponeWindowIndexedByDate(
        int $domainId,
        DateTimeImmutable $startDate,
    ): array {
        return $this->inMemoryCache->getOrSaveValue(
            static::CLOSED_DAYS_CACHE_NAMESPACE,
            function () use ($domainId, $startDate): array {
                $closedDays = $this->closedDayFacade->getClosedDaysWithEagerLoadedExcludedStores(
                    $domainId,
                    $startDate,
                    $startDate->modify(sprintf('+%d days', static::MAX_POSTPONE_DAYS)),
                );

                $closedDaysIndexedByDate = [];

                foreach ($closedDays as $closedDay) {
                    $closedDaysIndexedByDate[$closedDay->getDate()->format(static::DATE_INDEX_FORMAT)][] = $closedDay;
                }

                return $closedDaysIndexedByDate;
            },
            $domainId,
            $startDate->format(static::DATE_INDEX_FORMAT),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[] $closedDaysOnDate
     */
    protected function isDeliveryAllowedOnDate(
        Transport $transport,
        DateTimeImmutable $deliveryDate,
        int $domainId,
        ?Store $store,
        array $closedDaysOnDate,
    ): bool {
        if ($transport->isPersonalPickup() && $store !== null) {
            return !$this->isStoreClosedOnDate($store, $deliveryDate, $domainId, $closedDaysOnDate);
        }

        return !$this->isDeliveryBlockedByDayOfWeek($transport, $deliveryDate)
            && !$this->isDeliveryBlockedByClosedDays($transport, $closedDaysOnDate);
    }

    protected function isDeliveryBlockedByDayOfWeek(Transport $transport, DateTimeImmutable $deliveryDate): bool
    {
        return !$transport->deliversOnDayOfWeek((int)$deliveryDate->format('N'));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[] $closedDaysOnDate
     */
    protected function isDeliveryBlockedByClosedDays(Transport $transport, array $closedDaysOnDate): bool
    {
        return array_any(
            $closedDaysOnDate,
            fn (ClosedDay $closedDay): bool => !$this->isTransportDeliveringOnClosedDay($transport, $closedDay),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[] $closedDaysOnDate
     */
    protected function isStoreClosedOnDate(
        Store $store,
        DateTimeImmutable $deliveryDate,
        int $domainId,
        array $closedDaysOnDate,
    ): bool {
        return $this->isStoreClosedByClosedDays($store, $closedDaysOnDate)
            || $this->isStoreClosedByOpeningHours($store, $deliveryDate, $domainId);
    }

    protected function isStoreClosedByOpeningHours(Store $store, DateTimeImmutable $deliveryDate, int $domainId): bool
    {
        $dayOfWeek = (int)$deliveryDate->format('N');

        if (!$this->storeOpeningHoursProvider->isStoreOpenOnDayOfWeek($store, $dayOfWeek)) {
            return true;
        }

        if ($deliveryDate->format(static::DATE_INDEX_FORMAT) !== $this->getToday($domainId)->format(static::DATE_INDEX_FORMAT)) {
            return false;
        }

        $nowTime = $this->clock->now()
            ->setTimezone($this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId))
            ->format('H:i');

        return !$this->storeOpeningHoursProvider->isStoreOpenAfterTimeOnDayOfWeek($store, $dayOfWeek, $nowTime);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    protected function getStoresByDomainIdCached(int $domainId): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::STORES_CACHE_NAMESPACE,
            fn (): array => $this->storeFacade->getStoresByDomainIdWithEagerLoadedOpeningHours($domainId),
            $domainId,
        );
    }

    protected function isTransportDeliveringOnClosedDay(Transport $transport, ClosedDay $closedDay): bool
    {
        return $closedDay->isPublicHoliday()
            ? $transport->deliversOnPublicHolidays()
            : $transport->deliversOnInternalClosedDays();
    }

    /**
     * The store is closed unless it is excluded from every given closed day
     *
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[] $closedDays
     */
    protected function isStoreClosedByClosedDays(Store $store, array $closedDays): bool
    {
        return array_any(
            $closedDays,
            static fn (ClosedDay $closedDay): bool => !in_array($store, $closedDay->getExcludedStores(), true),
        );
    }

    protected function getToday(int $domainId): DateTimeImmutable
    {
        return $this->getStartOfDay($this->clock->now(), $domainId);
    }

    /**
     * All the delivery date arithmetic (weekends, closed days) must work with calendar days
     * as the customer sees them, so every date is first normalized to the domain display timezone
     */
    protected function getStartOfDay(DateTimeImmutable $dateTime, int $domainId): DateTimeImmutable
    {
        return $dateTime
            ->setTimezone($this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId))
            ->modify('midnight');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    protected function getQuantifiedProductsAwaitingRestocking(array $quantifiedProducts, int $domainId): array
    {
        if ($quantifiedProducts === []) {
            return [];
        }

        $stockQuantitiesIndexedByProductId = $this->productAvailabilityFacade
            ->getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId(
                array_map(static fn (QuantifiedProduct $quantifiedProduct): Product => $quantifiedProduct->getProduct(), $quantifiedProducts),
                $domainId,
            );

        return array_filter(
            $quantifiedProducts,
            static fn (QuantifiedProduct $quantifiedProduct): bool => $quantifiedProduct->getQuantity() > $stockQuantitiesIndexedByProductId[$quantifiedProduct->getProduct()->getId()],
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $awaitedQuantifiedProducts
     */
    protected function findWorstExpectedRestockingDate(
        array $awaitedQuantifiedProducts,
        int $domainId,
    ): ?DateTimeImmutable {
        $worstExpectedRestockingDate = null;

        foreach ($awaitedQuantifiedProducts as $quantifiedProduct) {
            $expectedRestockingDate = $this->productAvailabilityFacade->findValidExpectedRestockingDate(
                $quantifiedProduct->getProduct(),
                $domainId,
            );

            if ($expectedRestockingDate === null) {
                return null;
            }

            if ($worstExpectedRestockingDate === null || $expectedRestockingDate > $worstExpectedRestockingDate) {
                $worstExpectedRestockingDate = $expectedRestockingDate;
            }
        }

        return $worstExpectedRestockingDate;
    }
}
