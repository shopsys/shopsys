<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Product\Pricing\ProductPriceCalculation;
use App\Model\Product\Type\ProductType;
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation
     * @param \App\Model\Product\Pricing\QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \App\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
     */
    public function __construct(
        QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation,
        TransportPriceCalculation $transportPriceCalculation,
        PaymentPriceCalculation $paymentPriceCalculation,
        OrderPriceCalculation $orderPriceCalculation,
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        ProductPriceCalculation $productPriceCalculation
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
        ?ProductType $productType = null
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

        $productsPrice = $this->getProductsPrice($quantifiedItemsPrices, $quantifiedItemsDiscounts);

        if ($transport !== null) {
            $transportPrice = $this->transportPriceCalculation->calculatePrice(
                $transport,
                $currency,
                $productsPrice,
                $domainId
            );
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

        return new OrderPreview(
            $quantifiedProducts,
            $quantifiedItemsPrices,
            $quantifiedItemsDiscounts,
            $productsPrice,
            $totalPrice,
            $totalProductHighPrice,
            $transport,
            $transportPrice,
            $payment,
            $paymentPrice,
            $roundingPrice,
            $promoCodeDiscountPercent,
            $productType
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
}
