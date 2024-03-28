<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductDiscountCalculation as BaseQuantifiedProductDiscountCalculation;

/**
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price[] calculateDiscountsPerProductRoundedByCurrency(\Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts, \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices, \App\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price[] calculateDiscountPercentPrices(\Shopsys\FrameworkBundle\Model\Pricing\Price[] $discountsPerProduct, \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts, \App\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct, \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit $promoCodeLimit, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price[] calculateDiscountNominalPrices(\Shopsys\FrameworkBundle\Model\Pricing\Price[] $discountsPerProduct, \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts, \App\Model\Order\PromoCode\PromoCode[] $promoCodePerProduct, \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit $promoCodeLimit, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency)
 */
class QuantifiedProductDiscountCalculation extends BaseQuantifiedProductDiscountCalculation
{
}
