<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Payment;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class CartPaymentFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly CartPaymentDataFactory $cartPaymentDataFactory,
    ) {
    }

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

    public function unsetCartPayment(Cart $cart): void
    {
        $this->updatePaymentInCart($cart, null, null);
    }

    public function setPaymentWatchedPrice(Cart $cart, PriceInterface $paymentWatchedPrice): void
    {
        $cart->setPaymentWatchedPrice($paymentWatchedPrice->getPriceWithVat());
        $cart->setPaymentWatchedPriceWithoutVat($paymentWatchedPrice->getPriceWithoutVat());
        $this->entityManager->flush();
    }
}
