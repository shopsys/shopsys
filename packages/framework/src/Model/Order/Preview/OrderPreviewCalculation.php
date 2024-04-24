<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Preview;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductDiscountCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Store\Store;
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
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     */
    public function __construct(
        protected readonly QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        protected readonly QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly OrderPriceCalculation $orderPriceCalculation,
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
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
     * @param \Shopsys\FrameworkBundle\Model\Store\Store|null $personalPickupStore
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null $promoCode
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
        ?Store $personalPickupStore = null,
        ?PromoCode $promoCode = null,
    ): OrderPreview {
        $promoCodePerProduct = $this->currentPromoCodeFacade->getPromoCodePerProductByDomainId($quantifiedProducts, $domainId, $promoCode);
        $quantifiedItemsPricesWithoutDiscount = $this->quantifiedProductPriceCalculation->calculatePrices(
            $quantifiedProducts,
            $domainId,
            $customerUser,
        );

        $quantifiedItemsDiscounts = $this->quantifiedProductDiscountCalculation->calculateDiscountsPerProductRoundedByCurrency(
            $quantifiedProducts,
            $quantifiedItemsPricesWithoutDiscount,
            $promoCodePerProduct,
            $currency,
        );

        $productsPrice = $this->getProductsPrice($quantifiedItemsPricesWithoutDiscount, $quantifiedItemsDiscounts);

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

        $totalPriceDiscount = $this->getTotalPriceDiscount($quantifiedItemsDiscounts);

        $totalPriceWithoutDiscountTransportAndPayment = $this->getTotalPriceWithoutDiscountTransportAndPayment(
            $quantifiedItemsPricesWithoutDiscount,
        );

        return new OrderPreview(
            $quantifiedProducts,
            $quantifiedItemsPricesWithoutDiscount,
            $quantifiedItemsDiscounts,
            $productsPrice,
            $totalPrice,
            $totalPriceDiscount,
            $totalPriceWithoutDiscountTransportAndPayment,
            $transport,
            $transportPrice,
            $payment,
            $paymentPrice,
            $roundingPrice,
            $promoCodeDiscountPercent,
            $personalPickupStore,
            $promoCode,
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $quantifiedItemsDiscounts
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function getTotalPriceDiscount(array $quantifiedItemsDiscounts): Price
    {
        $totalDiscount = Price::zero();

        foreach ($quantifiedItemsDiscounts as $quantifiedItemDiscount) {
            if ($quantifiedItemDiscount !== null) {
                $totalDiscount = $totalDiscount->add($quantifiedItemDiscount);
            }
        }

        return $totalDiscount;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function getTotalPriceWithoutDiscountTransportAndPayment(array $quantifiedItemsPrices): Price
    {
        $totalPriceWithoutDiscountTransportAndPayment = Price::zero();

        foreach ($quantifiedItemsPrices as $quantifiedItemPrice) {
            if ($quantifiedItemPrice !== null) {
                $totalPriceWithoutDiscountTransportAndPayment = $totalPriceWithoutDiscountTransportAndPayment->add($quantifiedItemPrice->getTotalPrice());
            }
        }

        return $totalPriceWithoutDiscountTransportAndPayment;
    }
}
