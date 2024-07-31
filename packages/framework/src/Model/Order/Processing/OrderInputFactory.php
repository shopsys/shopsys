<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\OrderItemNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddPaymentMiddleware;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\PersonalPickupPointMiddleware;

class OrderInputFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput
     */
    public function create(DomainConfig $domainConfig): OrderInput
    {
        return new OrderInput($domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput
     */
    public function createFromCart(Cart $cart, DomainConfig $domainConfig): OrderInput
    {
        $orderInput = $this->create($domainConfig);

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

        $orderInput->setCustomerUser($cart->getCustomerUser());

        foreach ($cart->getAllAppliedPromoCodes() as $promoCode) {
            $orderInput->addPromoCode($promoCode);
        }

        return $orderInput;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput
     */
    public function createFromOrder(Order $order, DomainConfig $domainConfig): OrderInput
    {
        $orderInput = $this->create($domainConfig);
d($order->getQuantifiedProducts());
        foreach ($order->getQuantifiedProducts() as $quantifiedProduct) {
            $orderInput->addProduct(
                $quantifiedProduct->getProduct(),
                $quantifiedProduct->getQuantity(),
            );
        }

        try {
            $payment = $order->getPayment();
        } catch (OrderItemNotFoundException) {
            $payment = null;
        }

        try {
            $transport = $order->getTransport();
        } catch (OrderItemNotFoundException) {
            $transport = null;
        }

        $orderInput->setPayment($payment);
        $orderInput->setTransport($transport);

        $orderInput->addAdditionalData(PersonalPickupPointMiddleware::ADDITIONAL_DATA_PICKUP_PLACE_IDENTIFIER, $order->getPickupPlaceIdentifier());
        $orderInput->addAdditionalData(AddPaymentMiddleware::ADDITIONAL_DATA_GOPAY_BANK_SWIFT, $order->getGoPayBankSwift());

        $orderInput->setCustomerUser($order->getCustomerUser());

        foreach ($order->getAllAppliedPromoCodes() as $promoCode) {
            $orderInput->addPromoCode($promoCode);
        }

        return $orderInput;
    }
}
