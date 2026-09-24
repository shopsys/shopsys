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

    public function updateTransportInCart(Cart $cart, ?string $transportUuid, ?string $pickupPlaceIdentifier): void
    {
        if ($transportUuid !== null) {
            $cartTransportData = $this->cartTransportDataFactory->create($cart, $transportUuid, $pickupPlaceIdentifier);
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
        $cart->setTransportWatchedPrice($transportWatchedPrice->getPriceWithVat());
        $cart->setTransportWatchedPriceWithoutVat($transportWatchedPrice->getPriceWithoutVat());
        $this->entityManager->flush();
    }

    public function unsetPickupPlaceIdentifierFromCart(Cart $cart): void
    {
        $cart->unsetPickupPlaceIdentifier();
        $this->entityManager->flush();
    }
}
