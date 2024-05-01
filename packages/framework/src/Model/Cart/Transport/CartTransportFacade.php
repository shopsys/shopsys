<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;

class CartTransportFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportDataFactory $cartTransportDataFactory
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        protected CartTransportDataFactory $cartTransportDataFactory,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param string|null $transportUuid
     * @param string|null $pickupPlaceIdentifier
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function unsetCartTransport(Cart $cart): void
    {
        $this->updateTransportInCart($cart, null, null);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $transportWatchedPrice
     */
    public function setTransportWatchedPrice(Cart $cart, Money $transportWatchedPrice): void
    {
        $cart->setTransportWatchedPrice($transportWatchedPrice);
        $this->entityManager->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function unsetPickupPlaceIdentifierFromCart(Cart $cart): void
    {
        $cart->unsetPickupPlaceIdentifier();
        $this->entityManager->flush();
    }
}
