<?php

declare(strict_types=1);

namespace App\Model\Cart\Payment;

use App\Model\Cart\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;

class CartPaymentFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Model\Cart\Payment\CartPaymentDataFactory $cartPaymentDataFactory
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CartPaymentDataFactory $cartPaymentDataFactory,
    ) {
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
