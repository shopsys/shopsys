<?php

declare(strict_types=1);

namespace App\Model\Cart\Transport;

use App\Model\Cart\Cart;
use Doctrine\ORM\EntityManagerInterface;

class CartTransportFacade
{
    /**
     * @var \App\Model\Cart\Transport\CartTransportDataFactory
     */
    private CartTransportDataFactory $cartTransportDataFactory;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param \App\Model\Cart\Transport\CartTransportDataFactory $cartTransportDataFactory
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(CartTransportDataFactory $cartTransportDataFactory, EntityManagerInterface $entityManager)
    {
        $this->cartTransportDataFactory = $cartTransportDataFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * @param \App\Model\Cart\Cart $cart
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
}
