<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Pricing\ProductPriceCalculation;
use App\Model\Product\Type\ProductType;
use App\Model\Stock\Stock;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview as BaseOrderPreview;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewCalculation as BaseOrderPreviewCalculation;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
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
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price|null calculateRoundingPrice(\App\Model\Payment\Payment $payment, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency, \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice, \Shopsys\FrameworkBundle\Model\Pricing\Price|null $transportPrice, \Shopsys\FrameworkBundle\Model\Pricing\Price|null $paymentPrice)
 * @property \App\Model\Transport\TransportPriceCalculation $transportPriceCalculation
 * @property \App\Model\Product\Pricing\QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation
 */
class OrderPreviewCalculation extends BaseOrderPreviewCalculation
{
    /**
     * @var \App\Model\Order\PromoCode\CurrentPromoCodeFacade
     */
    private $currentPromoCodeFacade;

    /**
     * @var \App\Model\Product\Pricing\ProductPriceCalculation
     */
    private $productPriceCalculation;

    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @param \App\Model\Product\Pricing\QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation
     * @param \App\Model\Product\Pricing\QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation
     * @param \App\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \App\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     */
    public function __construct(
        QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation,
        TransportPriceCalculation $transportPriceCalculation,
        PaymentPriceCalculation $paymentPriceCalculation,
        OrderPriceCalculation $orderPriceCalculation,
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        ProductPriceCalculation $productPriceCalculation,
        ProductAvailabilityFacade $productAvailabilityFacade
    ) {
        parent::__construct(
            $quantifiedProductPriceCalculation,
            $quantifiedProductDiscountCalculation,
            $transportPriceCalculation,
            $paymentPriceCalculation,
            $orderPriceCalculation
        );
        $this->currentPromoCodeFacade = $currentPromoCodeFacade;
        $this->productPriceCalculation = $productPriceCalculation;
        $this->productAvailabilityFacade = $productAvailabilityFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \App\Model\Transport\Transport|null $transport
     * @param \App\Model\Payment\Payment|null $payment
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $promoCodeDiscountPercent
     * @param \App\Model\Product\Type\ProductType|null $productType
     * @param \App\Model\Stock\Stock|null $personalPickupStock
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
        ?ProductType $productType = null,
        ?Stock $personalPickupStock = null
    ): BaseOrderPreview {
        $quantifiedItemsPrices = $this->quantifiedProductPriceCalculation->calculatePrices(
            $quantifiedProducts,
            $domainId,
            $customerUser
        );

        $promoCodeDiscountPercentPerProduct = $this->currentPromoCodeFacade->getPromoCodeDiscountPercentPerProductByDomainId($quantifiedProducts, $domainId);

        $quantifiedItemsDiscounts = $this->quantifiedProductDiscountCalculation->calculateDiscountsPerProductRoundedByCurrency(
            $quantifiedProducts,
            $quantifiedItemsPrices,
            $promoCodeDiscountPercentPerProduct,
            $currency
        );

        $quantifiedItemsDiscountPrices = $this->quantifiedProductDiscountCalculation->calculateDiscountPricesPerProductRoundedByCurrency(
            $quantifiedProducts,
            $quantifiedItemsPrices,
            $promoCodeDiscountPercentPerProduct,
            $currency
        );

        $productsPrice = $this->getProductsPrice($quantifiedItemsPrices, $quantifiedItemsDiscounts);

        $restToFreeTransportPrice = Money::zero();
        $percentageOfFreeTransport = 0;
        $transportForFree = false;
        if ($productType !== null && $productType->isFreeTransport($domainId)) {
            $freeTransportMinimalPrice = $productType->getFreeTransportMinimalPrice($domainId);
            if ($freeTransportMinimalPrice !== null) {
                $restToFreeTransportPrice = $freeTransportMinimalPrice->subtract($productsPrice->getPriceWithVat());

                if ((float)$freeTransportMinimalPrice->getAmount() === (float)0) {
                    $percentageOfFreeTransport = 100;
                } else {
                    $percentageOfFreeTransport = (int)floor($productsPrice->getPriceWithVat()->getAmount() / ($freeTransportMinimalPrice->getAmount() / 100));
                }

                if ($productsPrice->getPriceWithVat()->getAmount() > $freeTransportMinimalPrice->getAmount()) {
                    $transportForFree = true;
                }
            }
        }

        $productsFullPrice = $this->getProductsPrice($quantifiedItemsPrices, []);

        $totalPriceDiscount = $this->getTotalPriceDiscount($quantifiedItemsDiscounts);

        if ($transport !== null) {
            if ($transportForFree) {
                $transportPrice = Price::zero();
            } else {
                $transportPrice = $this->transportPriceCalculation->calculatePrice(
                    $transport,
                    $currency,
                    $productsPrice,
                    $domainId
                );
            }
        } else {
            $transportPrice = null;
        }

        if ($payment !== null) {
            $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
                $payment,
                $currency,
                $productsPrice,
                $domainId
            );
            $roundingPrice = $this->calculateRoundingPrice(
                $payment,
                $currency,
                $productsPrice,
                $transportPrice,
                $paymentPrice
            );
        } else {
            $paymentPrice = null;
            $roundingPrice = null;
        }

        $totalPrice = $this->calculateTotalPrice(
            $productsPrice,
            $transportPrice,
            $paymentPrice,
            $roundingPrice
        );

        $totalProductHighPrice = $this->calculateTotalProductsHighPriceByDomain($quantifiedProducts, $domainId);
        $promoCodeCode = $this->currentPromoCodeFacade->getPromoCodeCode();
        $productsAvailability = $this->getProductsAvailability($quantifiedProducts, $domainId);
        $promoCodeIdentifier = $this->currentPromoCodeFacade->getPromoCodeIdentifier();

        return new OrderPreview(
            $quantifiedProducts,
            $quantifiedItemsPrices,
            $quantifiedItemsDiscounts,
            $productsPrice,
            $totalPrice,
            $totalProductHighPrice,
            $productsAvailability,
            $quantifiedItemsDiscountPrices,
            $productsFullPrice,
            $totalPriceDiscount,
            $transport,
            $transportPrice,
            $payment,
            $paymentPrice,
            $roundingPrice,
            $promoCodeDiscountPercent,
            $productType,
            $personalPickupStock,
            $restToFreeTransportPrice,
            $percentageOfFreeTransport,
            $transportForFree,
            $promoCodeCode,
            $promoCodeIdentifier
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private function calculateTotalProductsHighPriceByDomain(array $quantifiedProducts, int $domainId): Price
    {
        $totalHighPrice = Price::zero();
        foreach ($quantifiedProducts as $quantifiedProduct) {
            $productHighPrice = $this->productPriceCalculation->calculateProductNonSellingPrice(
                $quantifiedProduct->getProduct(),
                $domainId,
                $quantifiedProduct->getQuantity()
            );

            $totalHighPrice = $totalHighPrice->add($productHighPrice);
        }

        return $totalHighPrice;
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
                $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId(
                    $product,
                    $domainId
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
     * @param \App\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $quantifiedItemsDiscounts
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function getProductsPrice(array $quantifiedItemsPrices, array $quantifiedItemsDiscounts): Price
    {
        $finalPrice = Price::zero();
        $totalDiscount = $this->getTotalPriceDiscount($quantifiedItemsDiscounts);

        if ($totalDiscount->getPriceWithVat()->isGreaterThan(Money::zero())) {
            foreach ($quantifiedItemsPrices as $quantifiedItemPrice) {
                $finalPrice = $finalPrice->add($quantifiedItemPrice->getTotalHighPrice());
            }
            $finalPrice = $finalPrice->subtract($totalDiscount);
        } else {
            foreach ($quantifiedItemsPrices as $quantifiedItemPrice) {
                $finalPrice = $finalPrice->add($quantifiedItemPrice->getTotalPrice());
            }
        }

        return $finalPrice;
    }
}
