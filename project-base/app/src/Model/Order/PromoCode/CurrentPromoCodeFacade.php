<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade as BaseCurrentPromoCodeFacade;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method __construct(\App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository $promoCodeProductRepository, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Order\PromoCode\ProductPromoCodeFiller $productPromoCodeFiller, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitResolver $promoCodeLimitByCartTotalResolver, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository $promoCodePricingGroupRepository)
 * @method validatePromoCodeDatetime(\App\Model\Order\PromoCode\PromoCode $promoCode)
 * @method validateRemainingUses(\App\Model\Order\PromoCode\PromoCode $promoCode)
 * @method validatePromoCodeByProductsInCart(\App\Model\Order\PromoCode\PromoCode $promoCode, \App\Model\Cart\Cart $cart)
 * @method validateLimit(\App\Model\Order\PromoCode\PromoCode $promoCode, \App\Model\Cart\Cart $cart)
 * @method validatePromoCodeByFlags(\App\Model\Order\PromoCode\PromoCode $promoCode, \App\Model\Cart\Cart $cart)
 * @method \App\Model\Order\PromoCode\PromoCode[] getPromoCodePerProductByDomainId(\Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts, int $domainId, \App\Model\Order\PromoCode\PromoCode|null $promoCode = null)
 * @method validatePricingGroup(\App\Model\Order\PromoCode\PromoCode $promoCode)
 * @method \App\Model\Order\PromoCode\PromoCode getValidatedPromoCode(string $enteredCode, \App\Model\Cart\Cart $cart)
 */
class CurrentPromoCodeFacade extends BaseCurrentPromoCodeFacade
{
}
