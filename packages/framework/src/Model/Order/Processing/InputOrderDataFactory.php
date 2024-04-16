<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddPaymentMiddleware;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\PersonalPickupPointMiddleware;

class InputOrderDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\InputOrderData
     */
    public function create(): InputOrderData
    {
        return new InputOrderData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\InputOrderData
     */
    public function createFromCart(Cart $cart): InputOrderData
    {
        $inputOrderData = $this->create();

        foreach ($cart->getQuantifiedProducts() as $quantifiedProduct) {
            $inputOrderData->addProduct(
                $quantifiedProduct->getProduct(),
                $quantifiedProduct->getQuantity(),
            );
        }

        $inputOrderData->setPayment($cart->getPayment());
        $inputOrderData->setTransport($cart->getTransport());

        $inputOrderData->addAdditionalData(PersonalPickupPointMiddleware::ADDITIONAL_DATA_PICKUP_PLACE_IDENTIFIER, $cart->getPickupPlaceIdentifier());
        $inputOrderData->addAdditionalData(AddPaymentMiddleware::ADDITIONAL_DATA_GOPAY_BANK_SWIFT, $cart->getPaymentGoPayBankSwift());

        foreach ($cart->getAllAppliedPromoCodes() as $promoCode) {
            $inputOrderData->addPromoCode($promoCode);
        }

        return $inputOrderData;
    }
}
