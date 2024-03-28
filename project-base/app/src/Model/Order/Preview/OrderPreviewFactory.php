<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory as BaseOrderPreviewFactory;

/**
 * @property \App\Model\Order\Preview\OrderPreviewCalculation $orderPreviewCalculation
 * @property \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
 * @property \App\Model\Cart\CartFacade $cartFacade
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method __construct(\App\Model\Order\Preview\OrderPreviewCalculation $orderPreviewCalculation, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\Model\Cart\CartFacade $cartFacade, \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview create(\Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency, int $domainId, \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts, \App\Model\Transport\Transport|null $transport = null, \App\Model\Payment\Payment|null $payment = null, \App\Model\Customer\User\CustomerUser|null $customerUser = null, string|null $promoCodeDiscountPercent = null, \Shopsys\FrameworkBundle\Model\Store\Store|null $personalPickupStore = null, \App\Model\Order\PromoCode\PromoCode|null $promoCode = null)
 */
class OrderPreviewFactory extends BaseOrderPreviewFactory
{
}
