<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddPaymentMiddleware;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\PersonalPickupPointMiddleware;

class OrderInputFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput
     */
    public function create(): OrderInput
    {
        return new OrderInput();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput
     */
    public function createFromCart(Cart $cart): OrderInput
    {
        $orderInput = $this->create();

        foreach ($cart->getQuantifiedProducts() as $quantifiedProduct) {
            $orderInput->addProduct(
                $quantifiedProduct->getProduct(),
                $quantifiedProduct->getQuantity(),
            );
        }

        $orderInput->setPayment($cart->getPayment());
        $orderInput->setTransport($cart->getTransport());

        $orderInput->addAdditionalData(PersonalPickupPointMiddleware::ADDITIONAL_DATA_PICKUP_PLACE_IDENTIFIER, $cart->getPickupPlaceIdentifier());
        $orderInput->addAdditionalData(AddPaymentMiddleware::ADDITIONAL_DATA_GOPAY_BANK_SWIFT, $cart->getPaymentGoPayBankSwift());

        foreach ($cart->getAllAppliedPromoCodes() as $promoCode) {
            $orderInput->addPromoCode($promoCode);
        }

        return $orderInput;
    }
}
