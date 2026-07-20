<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\DeliveryDate;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class TransportExpectedDeliveryDateCalculation
{
    public function __construct(
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly ClockInterface $clock,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
    ) {
    }

    /**
     * Returns the expected delivery date for the given transport.
     * Without a cart (or when no cart item waits for restocking), the date is today plus the transport
     * delivery time, otherwise the worst expected restocking date is used as the dispatch day instead of today.
     * Null is returned only when no date can be promised (an awaited item has no valid restocking date).
     */
    public function calculateExpectedDeliveryDate(
        Transport $transport,
        ?Cart $cart,
        int $domainId,
    ): ?DateTimeImmutable {
        $awaitedCartItems = $cart === null ? [] : $this->getCartItemsAwaitingRestocking($cart, $domainId);

        if ($awaitedCartItems === []) {
            $dispatchDate = $this->getToday($domainId);
        } else {
            $dispatchDate = $this->findWorstExpectedRestockingDate($awaitedCartItems, $domainId);

            if ($dispatchDate === null) {
                return null;
            }
        }

        return $dispatchDate->modify(sprintf('+%d days', $transport->getDaysUntilDelivery()));
    }

    protected function getToday(int $domainId): DateTimeImmutable
    {
        return $this->clock->now()
            ->setTimezone($this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId))
            ->modify('midnight');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    protected function getCartItemsAwaitingRestocking(Cart $cart, int $domainId): array
    {
        return array_filter(
            $cart->getItems(),
            function (CartItem $cartItem) use ($domainId): bool {
                $stockQuantity = $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId(
                    $cartItem->getProduct(),
                    $domainId,
                ) ?? 0;

                return $cartItem->getQuantity() > $stockQuantity;
            },
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
