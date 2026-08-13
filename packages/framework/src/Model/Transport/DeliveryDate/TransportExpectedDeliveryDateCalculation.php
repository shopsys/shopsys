<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\DeliveryDate;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
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
        $storeSelectedInCart = $this->findStoreSelectedInCartForTransport($transport, $cart, $domainId);

        return $this->calculateDeliveryDate($transport, $cart, $domainId, $storeSelectedInCart);
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
        if (!$transport->isPersonalPickup()) {
            throw new TransportIsNotPersonalPickupException(
                'The expected delivery date for a store can only be calculated for a personal pickup transport.',
            );
        }

        return $this->calculateDeliveryDate($transport, $cart, $domainId, $store);
    }

    protected function calculateDeliveryDate(
        Transport $transport,
        ?Cart $cart,
        int $domainId,
        ?Store $store,
    ): ?DateTimeImmutable {
        // the dispatch date does not depend on the transport nor the store, so one cart resolves it just once
        // per request even when the date is being calculated for a whole transport listing or store picker
        $dispatchDate = $this->inMemoryCache->getOrSaveValue(
            static::DISPATCH_DATE_CACHE_NAMESPACE,
            fn (): ?DateTimeImmutable => $this->findDispatchDate($cart, $domainId),
            $this->getCartCacheKey($cart),
            $domainId,
        );

        if ($dispatchDate === null) {
            return null;
        }

        $closestPossibleDeliveryDate = $dispatchDate->modify(sprintf('+%d days', $transport->getDaysUntilDelivery()));

        $deliveryDate = $this->postponeToFirstAllowedDeliveryDay($transport, $closestPossibleDeliveryDate, $domainId, $store);

        return $deliveryDate?->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * The dispatch date is derived from the cart items, so their fingerprint is a part of the cache key —
     * a mutation modifying the cart and resolving the delivery date within the same request (mutating the
     * very same cart instance) gets a fresh value; the stocks are considered stable within a request
     */
    protected function getCartCacheKey(?Cart $cart): string
    {
        if ($cart === null) {
            return 'no-cart';
        }

        $cartItemParts = array_map(
            static fn (CartItem $cartItem): string => $cartItem->getProduct()->getId() . ':' . $cartItem->getQuantity(),
            $cart->getItems(),
        );

        return spl_object_id($cart) . '|' . implode(',', $cartItemParts);
    }

    protected function findStoreSelectedInCartForTransport(Transport $transport, ?Cart $cart, int $domainId): ?Store
    {
        if (
            $cart !== null
            && $transport->isPersonalPickup()
            && $cart->getTransport()?->getId() === $transport->getId()
            && $cart->getPickupPlaceIdentifier() !== null
        ) {
            return $this->storeFacade->findByUuidAndDomainId($cart->getPickupPlaceIdentifier(), $domainId);
        }

        return null;
    }

    /**
     * Returns the day the order can be dispatched — today, or the worst expected restocking date
     * of the awaited cart items; null when an awaited item has no valid restocking date
     */
    protected function findDispatchDate(?Cart $cart, int $domainId): ?DateTimeImmutable
    {
        $awaitedCartItems = $cart === null ? [] : $this->getCartItemsAwaitingRestocking($cart, $domainId);

        if ($awaitedCartItems === []) {
            return $this->getToday($domainId);
        }

        $worstExpectedRestockingDate = $this->findWorstExpectedRestockingDate($awaitedCartItems, $domainId);

        if ($worstExpectedRestockingDate === null) {
            return null;
        }

        return $this->getStartOfDay($worstExpectedRestockingDate, $domainId);
    }

    protected function postponeToFirstAllowedDeliveryDay(
        Transport $transport,
        DateTimeImmutable $deliveryDate,
        int $domainId,
        ?Store $store,
    ): ?DateTimeImmutable {
        $closedDaysIndexedByDate = $this->getClosedDaysForPostponeWindowIndexedByDate($domainId, $deliveryDate);

        $postponedDays = 0;

        while (
            $postponedDays < static::MAX_POSTPONE_DAYS
            && !$this->isDeliveryAllowedOnDate(
                $transport,
                $deliveryDate,
                $domainId,
                $store,
                $closedDaysIndexedByDate[$deliveryDate->format(static::DATE_INDEX_FORMAT)] ?? [],
            )
        ) {
            $deliveryDate = $deliveryDate->modify('+1 day');
            $postponedDays++;
        }

        if ($postponedDays === static::MAX_POSTPONE_DAYS) {
            return null;
        }

        return $deliveryDate;
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
        return !$this->isDeliveryBlockedByDayOfWeek($transport, $deliveryDate)
            && !$this->isDeliveryBlockedByClosedDays($transport, $closedDaysOnDate, $domainId, $store);
    }

    protected function isDeliveryBlockedByDayOfWeek(Transport $transport, DateTimeImmutable $deliveryDate): bool
    {
        return !$transport->deliversOnDayOfWeek((int)$deliveryDate->format('N'));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay[] $closedDaysOnDate
     */
    protected function isDeliveryBlockedByClosedDays(
        Transport $transport,
        array $closedDaysOnDate,
        int $domainId,
        ?Store $store,
    ): bool {
        $blockingClosedDays = array_filter(
            $closedDaysOnDate,
            fn (ClosedDay $closedDay): bool => !$this->isTransportDeliveringOnClosedDay($transport, $closedDay),
        );

        if ($blockingClosedDays === []) {
            return false;
        }

        if ($store === null && !$transport->isPersonalPickup()) {
            return true;
        }

        if ($store !== null) {
            return $this->isStoreClosedByClosedDays($store, $blockingClosedDays);
        }

        // a personal pickup is blocked only when every store on the domain is closed
        return array_all(
            $this->getStoresByDomainIdCached($domainId),
            fn (Store $domainStore): bool => $this->isStoreClosedByClosedDays($domainStore, $blockingClosedDays),
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    protected function getStoresByDomainIdCached(int $domainId): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::STORES_CACHE_NAMESPACE,
            fn (): array => $this->storeFacade->getStoresByDomainId($domainId),
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
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    protected function getCartItemsAwaitingRestocking(Cart $cart, int $domainId): array
    {
        $cartItems = $cart->getItems();

        $stockQuantitiesIndexedByProductId = $this->productAvailabilityFacade
            ->getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId(
                array_map(static fn (CartItem $cartItem): Product => $cartItem->getProduct(), $cartItems),
                $domainId,
            );

        return array_filter(
            $cartItems,
            static fn (CartItem $cartItem): bool => $cartItem->getQuantity() > $stockQuantitiesIndexedByProductId[$cartItem->getProduct()->getId()],
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[] $awaitedCartItems
     */
    protected function findWorstExpectedRestockingDate(array $awaitedCartItems, int $domainId): ?DateTimeImmutable
    {
        $worstExpectedRestockingDate = null;

        foreach ($awaitedCartItems as $cartItem) {
            $expectedRestockingDate = $this->productAvailabilityFacade->findValidExpectedRestockingDate(
                $cartItem->getProduct(),
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
