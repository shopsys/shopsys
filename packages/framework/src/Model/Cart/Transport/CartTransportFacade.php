<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Order;

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
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     * @param string|null $transportUuid
     * @param string|null $pickupPlaceIdentifier
     */
    public function updateTransportInCart(Order $cart, ?string $transportUuid, ?string $pickupPlaceIdentifier): void
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
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     */
    public function unsetCartTransport(Order $cart): void
    {
        $this->updateTransportInCart($cart, null, null);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $transportWatchedPrice
     */
    public function setTransportWatchedPrice(Order $cart, Money $transportWatchedPrice): void
    {
        $cart->setTransportWatchedPrice($transportWatchedPrice);
        $this->entityManager->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     */
    public function unsetPickupPlaceIdentifierFromCart(Order $cart): void
    {
        $cart->unsetPickupPlaceIdentifier();
        $this->entityManager->flush();
    }
}
