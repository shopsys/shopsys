<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Payment;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;

class CartPaymentFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentDataFactory $cartPaymentDataFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly CartPaymentDataFactory $cartPaymentDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
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
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function unsetCartPayment(Cart $cart): void
    {
        $this->updatePaymentInCart($cart, null, null);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $paymentWatchedPrice
     */
    public function setPaymentWatchedPrice(Cart $cart, Money $paymentWatchedPrice): void
    {
        $cart->setPaymentWatchedPrice($paymentWatchedPrice);
        $this->entityManager->flush();
    }
}
