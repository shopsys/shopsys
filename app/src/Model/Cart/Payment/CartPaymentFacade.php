<?php

declare(strict_types=1);

namespace App\Model\Cart\Payment;

use App\Model\Cart\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;

class CartPaymentFacade
{
    /**
     * @var \App\Model\Cart\Payment\CartPaymentDataFactory
     */
    private CartPaymentDataFactory $cartPaymentDataFactory;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Model\Cart\Payment\CartPaymentDataFactory $cartPaymentDataFactory
     */
    public function __construct(EntityManagerInterface $entityManager, CartPaymentDataFactory $cartPaymentDataFactory)
    {
        $this->entityManager = $entityManager;
        $this->cartPaymentDataFactory = $cartPaymentDataFactory;
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param string|null $paymentUuid
     * @param string|null $goPayBankSwift
     */
    public function updatePaymentInCart(Cart $cart, ?string $paymentUuid, ?string $goPayBankSwift): void
    {
        if ($paymentUuid !== null) {
            $cartPaymentData = $this->cartPaymentDataFactory->create($cart, $paymentUuid, $goPayBankSwift);
            $cart->editCartPayment($cartPaymentData);
        } else {
            $cart->unsetCartPayment();
        }

        $this->entityManager->flush();
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    public function unsetCartPayment(Cart $cart): void
    {
        $this->updatePaymentInCart($cart, null, null);
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $paymentWatchedPrice
     */
    public function setPaymentWatchedPrice(Cart $cart, Money $paymentWatchedPrice): void
    {
        $cart->setPaymentWatchedPrice($paymentWatchedPrice);
        $this->entityManager->flush();
    }
}
