<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Order\PromoCode\PromoCode;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Store\Store;
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
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     */
    public function __construct(
        QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation,
        TransportPriceCalculation $transportPriceCalculation,
        PaymentPriceCalculation $paymentPriceCalculation,
        OrderPriceCalculation $orderPriceCalculation,
        private readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
        private readonly ProductAvailabilityFacade $productAvailabilityFacade,
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
     * @param \App\Model\Store\Store|null $personalPickupStore
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
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @return string[]
     */
    protected function getProductsAvailability(array $quantifiedProducts, int $domainId): array
    {
        $availability = [];
        foreach ($quantifiedProducts as $quantifiedProduct) {
            /** @var \App\Model\Product\Product $product */
            $product = $quantifiedProduct->getProduct();
            $availability[$product->getId()] =
                $this->productAvailabilityFacade->getProductAvailabilityInformationByQuantifiedProductAndDomainId(
                    $quantifiedProduct,
                    $domainId,
                );
        }

        return $availability;
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
