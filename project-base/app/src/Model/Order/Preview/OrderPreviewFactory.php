<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Component\Deprecation\DeprecatedMethodException;
use App\Model\Order\PromoCode\PromoCode;
use App\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory as BaseOrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

/**
 * @property \App\Model\Order\Preview\OrderPreviewCalculation $orderPreviewCalculation
 * @property \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
 * @property \App\Model\Cart\CartFacade $cartFacade
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method __construct(\App\Model\Order\Preview\OrderPreviewCalculation $orderPreviewCalculation, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\Model\Cart\CartFacade $cartFacade, \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 */
class OrderPreviewFactory extends BaseOrderPreviewFactory
{
    /**
     * @deprecated use create() method instead
     * @param \App\Model\Transport\Transport|null $transport
     * @param \App\Model\Payment\Payment|null $payment
     * @param \App\Model\Store\Store|null $personalPickupStore
     * @return \App\Model\Order\Preview\OrderPreview
     */
    public function createForCurrentUser(
        ?Transport $transport = null,
        ?Payment $payment = null,
        ?Store $personalPickupStore = null,
    ): OrderPreview {
        throw new DeprecatedMethodException();
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
    public function create(
        Currency $currency,
        $domainId,
        array $quantifiedProducts,
        ?Transport $transport = null,
        ?Payment $payment = null,
        ?CustomerUser $customerUser = null,
        ?string $promoCodeDiscountPercent = null,
        ?Store $personalPickupStore = null,
        ?PromoCode $promoCode = null,
    ): OrderPreview {
        return $this->orderPreviewCalculation->calculatePreview(
            $currency,
            $domainId,
            $quantifiedProducts,
            $transport,
            $payment,
            $customerUser,
            $promoCodeDiscountPercent,
            $personalPickupStore,
            $promoCode,
        );
    }
}
