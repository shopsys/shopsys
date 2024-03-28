<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewCalculation as BaseOrderPreviewCalculation;

/**
 * @property \App\Model\Product\Pricing\QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price|null calculateRoundingPrice(\App\Model\Payment\Payment $payment, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency, \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice, \Shopsys\FrameworkBundle\Model\Pricing\Price|null $transportPrice = null, \Shopsys\FrameworkBundle\Model\Pricing\Price|null $paymentPrice = null)
 * @property \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
 * @property \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
 * @method __construct(\Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation, \App\Model\Product\Pricing\QuantifiedProductDiscountCalculation $quantifiedProductDiscountCalculation, \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation, \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation, \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation, \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade)
 * @method \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview calculatePreview(\Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency, int $domainId, \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts, \App\Model\Transport\Transport|null $transport = null, \App\Model\Payment\Payment|null $payment = null, \App\Model\Customer\User\CustomerUser|null $customerUser = null, string|null $promoCodeDiscountPercent = null, \Shopsys\FrameworkBundle\Model\Store\Store|null $personalPickupStore = null, \App\Model\Order\PromoCode\PromoCode|null $promoCode = null)
 */
class OrderPreviewCalculation extends BaseOrderPreviewCalculation
{
}
