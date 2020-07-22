<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Component\Domain\Domain;
use App\Model\Cart\CartFacade;
use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Order\PromoCode\PromoCodeLimitByCartTotalResolver;
use App\Model\Product\Type\ProductType;
use App\Model\Stock\Stock;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory as BaseOrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

/**
 * @property \App\Model\Order\Preview\OrderPreviewCalculation $orderPreviewCalculation
 * @property \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
 * @property \App\Model\Cart\CartFacade $cartFacade
 * @property \App\Component\Domain\Domain $domain
 */
class OrderPreviewFactory extends BaseOrderPreviewFactory
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCodeLimitByCartTotalResolver
     */
    private $promoCodeLimitByCartTotalResolver;

    /**
     * @param \App\Model\Order\Preview\OrderPreviewCalculation $orderPreviewCalculation
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \App\Model\Order\PromoCode\PromoCodeLimitByCartTotalResolver $promoCodeLimitByCartTotalResolver
     */
    public function __construct(
        OrderPreviewCalculation $orderPreviewCalculation,
        Domain $domain,
        CurrencyFacade $currencyFacade,
        CurrentCustomerUser $currentCustomerUser,
        CartFacade $cartFacade,
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        PromoCodeLimitByCartTotalResolver $promoCodeLimitByCartTotalResolver
    ) {
        parent::__construct(
            $orderPreviewCalculation,
            $domain,
            $currencyFacade,
            $currentCustomerUser,
            $cartFacade,
            $currentPromoCodeFacade
        );
        $this->promoCodeLimitByCartTotalResolver = $promoCodeLimitByCartTotalResolver;
    }

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
            $validEnteredPromoCodePercent = $this->promoCodeLimitByCartTotalResolver->getLimitByPromoCode($validEnteredPromoCode)->getPercent();
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
     * @param \App\Model\Product\Type\ProductType|null $productType
     * @param \App\Model\Stock\Stock|null $personalPickupStock
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
        ?ProductType $productType = null,
        ?Stock $personalPickupStock = null
    ): OrderPreview {
        return $this->orderPreviewCalculation->calculatePreview(
            $currency,
            $domainId,
            $quantifiedProducts,
            $transport,
            $payment,
            $customerUser,
            $promoCodeDiscountPercent,
            $productType,
            $personalPickupStock
        );
    }
}
