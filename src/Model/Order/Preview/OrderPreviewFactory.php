<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Product\Type\ProductType;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory as BaseOrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

/**
 * @property \App\Model\Order\Preview\OrderPreviewCalculation $orderPreviewCalculation
 * @property \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
 * @method __construct(\App\Model\Order\Preview\OrderPreviewCalculation $orderPreviewCalculation, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade, \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade)
 */
class OrderPreviewFactory extends BaseOrderPreviewFactory
{
    /**
     * @param \App\Model\Transport\Transport|null $transport
     * @param \App\Model\Payment\Payment|null $payment
     * @param \App\Model\Product\Type\ProductType|null $productType
     * @return \App\Model\Order\Preview\OrderPreview
     */
    public function createForCurrentUser(
        ?Transport $transport = null,
        ?Payment $payment = null,
        ?ProductType $productType = null
    ): OrderPreview {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());
        $validEnteredPromoCode = $this->currentPromoCodeFacade->getValidEnteredPromoCodeOrNull();
        $validEnteredPromoCodePercent = null;
        if ($validEnteredPromoCode !== null) {
            $validEnteredPromoCodePercent = $validEnteredPromoCode->getPercent();
        }

        return $this->create(
            $currency,
            $this->domain->getId(),
            $this->cartFacade->getQuantifiedProductsOfCurrentCustomer(),
            $transport,
            $payment,
            $this->currentCustomerUser->findCurrentCustomerUser(),
            $validEnteredPromoCodePercent,
            $productType
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
     * @param \App\Model\Product\Type\ProductType $productType
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
        ?ProductType $productType = null
    ): OrderPreview {
        return $this->orderPreviewCalculation->calculatePreview(
            $currency,
            $domainId,
            $quantifiedProducts,
            $transport,
            $payment,
            $customerUser,
            $promoCodeDiscountPercent,
            $productType
        );
    }
}
