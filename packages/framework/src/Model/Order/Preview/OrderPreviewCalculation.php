<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Preview;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductDiscountCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

class OrderPreviewCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     */
    public function __construct(
        protected readonly QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        protected readonly QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly OrderPriceCalculation $orderPriceCalculation,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport|null $transport
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment|null $payment
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $promoCodeDiscountPercent
     * @return \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview
     */
    public function calculatePreview(
        Currency $currency,
        int $domainId,
        array $quantifiedProducts,
        ?Transport $transport = null,
        ?Payment $payment = null,
        ?CustomerUser $customerUser = null,
        ?string $promoCodeDiscountPercent = null,
    ): OrderPreview {
        $quantifiedItemsPrices = $this->quantifiedProductPriceCalculation->calculatePrices(
            $quantifiedProducts,
            $domainId,
            $customerUser,
        );
        $quantifiedItemsDiscounts = $this->quantifiedProductDiscountCalculation->calculateDiscountsRoundedByCurrency(
            $quantifiedItemsPrices,
            $promoCodeDiscountPercent,
            $currency,
        );

        $productsPrice = $this->getProductsPrice($quantifiedItemsPrices, $quantifiedItemsDiscounts);

        if ($transport !== null) {
            $transportPrice = $this->transportPriceCalculation->calculatePrice(
                $transport,
                $currency,
                $productsPrice,
                $domainId,
            );
        } else {
            $transportPrice = null;
        }

        if ($payment !== null) {
            $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
                $payment,
                $currency,
                $productsPrice,
                $domainId,
            );
            $roundingPrice = $this->calculateRoundingPrice(
                $payment,
                $currency,
                $productsPrice,
                $transportPrice,
                $paymentPrice,
            );
        } else {
            $paymentPrice = null;
            $roundingPrice = null;
        }

        $totalPrice = $this->calculateTotalPrice(
            $productsPrice,
            $transportPrice,
            $paymentPrice,
            $roundingPrice,
        );

        return new OrderPreview(
            $quantifiedProducts,
            $quantifiedItemsPrices,
            $quantifiedItemsDiscounts,
            $productsPrice,
            $totalPrice,
            $transport,
            $transportPrice,
            $payment,
            $paymentPrice,
            $roundingPrice,
            $promoCodeDiscountPercent,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $transportPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $paymentPrice
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    protected function calculateRoundingPrice(
        Payment $payment,
        Currency $currency,
        Price $productsPrice,
        ?Price $transportPrice = null,
        ?Price $paymentPrice = null,
    ): ?Price {
        $totalPrice = $this->calculateTotalPrice(
            $productsPrice,
            $transportPrice,
            $paymentPrice,
            null,
        );

        return $this->orderPriceCalculation->calculateOrderRoundingPrice($payment, $currency, $totalPrice);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $transportPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $paymentPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $roundingPrice
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function calculateTotalPrice(
        Price $productsPrice,
        ?Price $transportPrice = null,
        ?Price $paymentPrice = null,
        ?Price $roundingPrice = null,
    ): Price {
        $totalPrice = Price::zero();

        $totalPrice = $totalPrice->add($productsPrice);

        if ($transportPrice !== null) {
            $totalPrice = $totalPrice->add($transportPrice);
        }

        if ($paymentPrice !== null) {
            $totalPrice = $totalPrice->add($paymentPrice);
        }

        if ($roundingPrice !== null) {
            $totalPrice = $totalPrice->add($roundingPrice);
        }

        return $totalPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $quantifiedItemsDiscounts
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function getProductsPrice(array $quantifiedItemsPrices, array $quantifiedItemsDiscounts): Price
    {
        $finalPrice = Price::zero();

        foreach ($quantifiedItemsPrices as $quantifiedItemPrice) {
            $finalPrice = $finalPrice->add($quantifiedItemPrice->getTotalPrice());
        }

        foreach ($quantifiedItemsDiscounts as $quantifiedItemDiscount) {
            if ($quantifiedItemDiscount !== null) {
                $finalPrice = $finalPrice->subtract($quantifiedItemDiscount);
            }
        }

        return $finalPrice;
    }
}
