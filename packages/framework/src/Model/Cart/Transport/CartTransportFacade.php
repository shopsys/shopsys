<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class CartTransportFacade
{
    public function __construct(
        protected CartTransportDataFactory $cartTransportDataFactory,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array{openingHoursOfDays: array<int, array{dayOfWeek: int, openingHoursRanges: array<int, array{openingTime: string, closingTime: string}>}>, exceptionDays: array<int, array{from: string, to: string|null, openingHoursRanges: array<int, array{openingTime: string, closingTime: string}>}>}|null $pickupPlaceOpeningHours
     */
    public function updateTransportInCart(
        Cart $cart,
        ?string $transportUuid,
        ?string $pickupPlaceIdentifier,
        ?array $pickupPlaceOpeningHours = null,
    ): void {
        if ($transportUuid !== null) {
            $cartTransportData = $this->cartTransportDataFactory->create($cart, $transportUuid, $pickupPlaceIdentifier, $pickupPlaceOpeningHours);
            $cart->editCartTransport($cartTransportData);
        } else {
            $cart->unsetCartTransport();
        }

        $this->entityManager->flush();
    }

    public function unsetCartTransport(Cart $cart): void
    {
        $this->updateTransportInCart($cart, null, null);
    }

    public function setTransportWatchedPrice(Cart $cart, PriceInterface $transportWatchedPrice): void
    {
        $cart->setTransportWatchedPrice($transportWatchedPrice);
        $this->entityManager->flush();
    }

    public function unsetPickupPlaceIdentifierFromCart(Cart $cart): void
    {
        $cart->unsetPickupPlaceIdentifier();
        $this->entityManager->flush();
    }
}
