<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade as BaseCurrentPromoCodeFacade;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method __construct(\App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository $promoCodeProductRepository, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Order\PromoCode\ProductPromoCodeFiller $productPromoCodeFiller, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository $promoCodePricingGroupRepository, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeApplicableProductsTotalPriceCalculator $promoCodeApplicableProductsTotalPriceCalculator)
 * @method validatePromoCodeDatetime(\App\Model\Order\PromoCode\PromoCode $promoCode)
 * @method validateRemainingUses(\App\Model\Order\PromoCode\PromoCode $promoCode)
 * @method validateLimit(\App\Model\Order\PromoCode\PromoCode $promoCode, \Shopsys\FrameworkBundle\Model\Pricing\Price $price)
 * @method array<int,\App\Model\Order\PromoCode\PromoCode> getPromoCodePerProductByDomainId(\Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts, int $domainId, \App\Model\Order\PromoCode\PromoCode|null $promoCode = null)
 * @method validatePricingGroup(\App\Model\Order\PromoCode\PromoCode $promoCode)
 * @method \App\Model\Order\PromoCode\PromoCode getValidatedPromoCode(string $enteredCode, \App\Model\Cart\Cart $cart)
 * @method int[] validatePromoCodeByProductsInCart(\App\Model\Order\PromoCode\PromoCode $promoCode, \App\Model\Product\Product[] $products)
 * @method int[] validatePromoCodeByFlags(\App\Model\Order\PromoCode\PromoCode $promoCode, \App\Model\Product\Product[] $products)
 * @method int[] validatePromoCode(\App\Model\Order\PromoCode\PromoCode $promoCode, \Shopsys\FrameworkBundle\Model\Pricing\Price $totalProductPrice, \App\Model\Product\Product[] $products)
 */
class CurrentPromoCodeFacade extends BaseCurrentPromoCodeFacade
{
}
