<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview as BaseOrderPreview;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewCalculation as BaseOrderPreviewCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductDiscountCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

/**
 * @property \App\Model\Product\Pricing\QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price|null calculateRoundingPrice(\App\Model\Payment\Payment $payment, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency, \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice, \Shopsys\FrameworkBundle\Model\Pricing\Price|null $transportPrice = null, \Shopsys\FrameworkBundle\Model\Pricing\Price|null $paymentPrice = null)
 * @property \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
 */
class OrderPreviewCalculation extends BaseOrderPreviewCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation
     * @param \App\Model\Product\Pricing\QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     */
    public function __construct(
        QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation,
        TransportPriceCalculation $transportPriceCalculation,
        PaymentPriceCalculation $paymentPriceCalculation,
        OrderPriceCalculation $orderPriceCalculation,
        private readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
    ) {
        parent::__construct(
            $quantifiedProductPriceCalculation,
            $quantifiedProductDiscountCalculation,
            $transportPriceCalculation,
            $paymentPriceCalculation,
            $orderPriceCalculation,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \App\Model\Transport\Transport|null $transport
     * @param \App\Model\Payment\Payment|null $payment
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $promoCodeDiscountPercent
     * @param \Shopsys\FrameworkBundle\Model\Store\Store|null $personalPickupStore
     * @param \App\Model\Order\PromoCode\PromoCode|null $promoCode
     * @return \App\Model\Order\Preview\OrderPreview
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
    ): BaseOrderPreview {
        $promoCodePerProduct = $this->currentPromoCodeFacade->getPromoCodePerProductByDomainId($quantifiedProducts, $domainId, $promoCode);
        $quantifiedItemsPrices = $this->quantifiedProductPriceCalculation->calculatePrices(
            $quantifiedProducts,
            $domainId,
            $customerUser,
        );

        $quantifiedItemsDiscounts = $this->quantifiedProductDiscountCalculation->calculateDiscountsPerProductRoundedByCurrency(
            $quantifiedProducts,
            $quantifiedItemsPrices,
            $promoCodePerProduct,
            $currency,
        );

        $quantifiedItemsDiscountPrices = $this->quantifiedProductDiscountCalculation->calculateDiscountPricesPerProductRoundedByCurrency(
            $quantifiedItemsPrices,
            $quantifiedItemsDiscounts,
            $currency,
        );

        $productsPrice = $this->getProductsPrice($quantifiedItemsPrices, $quantifiedItemsDiscounts);
        $transportPrice = $this->getTransportPrice($currency, $domainId, $productsPrice, $transport);

        [$paymentPrice, $roundingPrice] = $this->getPaymentAndRoundingPrices(
            $currency,
            $productsPrice,
            $domainId,
            $payment,
            $transportPrice,
        );

        $totalPrice = $this->calculateTotalPrice(
            $productsPrice,
            $transportPrice,
            $paymentPrice,
            $roundingPrice,
        );

        $totalPriceDiscount = $this->getTotalPriceDiscount($quantifiedItemsDiscounts);

        $quantifiedItemsPricesWithoutDiscount = $this->quantifiedProductPriceCalculation->calculatePrices(
            $quantifiedProducts,
            $domainId,
            $customerUser,
        );
        $totalPriceWithoutDiscountTransportAndPayment = $this->getTotalPriceWithoutDiscountTransportAndPayment(
            $quantifiedItemsPricesWithoutDiscount,
        );

        return new OrderPreview(
            $quantifiedProducts,
            $quantifiedItemsPrices,
            $quantifiedItemsDiscounts,
            $productsPrice,
            $totalPrice,
            $quantifiedItemsDiscountPrices,
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
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param \App\Model\Transport\Transport|null $transport
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private function getTransportPrice(
        Currency $currency,
        int $domainId,
        Price $productsPrice,
        ?Transport $transport,
    ): ?Price {
        if ($transport === null) {
            return null;
        }

        return $this->transportPriceCalculation->calculatePrice(
            $transport,
            $currency,
            $productsPrice,
            $domainId,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param int $domainId
     * @param \App\Model\Payment\Payment|null $payment
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $transportPrice
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]|null[]
     */
    private function getPaymentAndRoundingPrices(
        Currency $currency,
        Price $productsPrice,
        int $domainId,
        ?Payment $payment,
        ?Price $transportPrice,
    ): array {
        if ($payment === null) {
            return [null, null];
        }

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

        return [$paymentPrice, $roundingPrice];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $quantifiedItemsDiscounts
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private function getTotalPriceDiscount(array $quantifiedItemsDiscounts): Price
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
    private function getTotalPriceWithoutDiscountTransportAndPayment(array $quantifiedItemsPrices): Price
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
