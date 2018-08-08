<?php

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderProduct;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;

class OrderPriceCalculation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation
     */
    private $orderItemPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Rounding
     */
    private $rounding;

    public function __construct(
        OrderItemPriceCalculation $orderItemPriceCalculation,
        Rounding $rounding
    ) {
        $this->orderItemPriceCalculation = $orderItemPriceCalculation;
        $this->rounding = $rounding;
    }

    public function getOrderTotalPrice(Order $order): \Shopsys\FrameworkBundle\Model\Order\OrderTotalPrice
    {
        $priceWithVat = 0;
        $priceWithoutVat = 0;
        $productPriceWithVat = 0;

        foreach ($order->getItems() as $orderItem) {
            $itemTotalPrice = $this->orderItemPriceCalculation->calculateTotalPrice($orderItem);

            $priceWithVat += $itemTotalPrice->getPriceWithVat();
            $priceWithoutVat += $itemTotalPrice->getPriceWithoutVat();

            if ($orderItem instanceof OrderProduct) {
                $productPriceWithVat += $itemTotalPrice->getPriceWithVat();
            }
        }

        return new OrderTotalPrice($priceWithVat, $priceWithoutVat, $productPriceWithVat);
    }

    public function calculateOrderRoundingPrice(
        Payment $payment,
        Currency $currency,
        Price $orderTotalPrice
    ): ?\Shopsys\FrameworkBundle\Model\Pricing\Price {
        if (!$payment->isCzkRounding() || $currency->getCode() !== Currency::CODE_CZK) {
            return null;
        }

        $roundingPrice = $this->rounding->roundPriceWithVat(
            round($orderTotalPrice->getPriceWithVat()) - $orderTotalPrice->getPriceWithVat()
        );
        if ($roundingPrice === 0.0) {
            return null;
        }

        return new Price($roundingPrice, $roundingPrice);
    }
}
