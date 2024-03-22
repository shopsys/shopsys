<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class CartPaymentDataFactory
{
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly OrderPreviewFactory $orderPreviewFactory,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param string $paymentUuid
     * @param string|null $goPayBankSwift
     * @return \Shopsys\FrontendApiBundle\Model\Cart\Payment\CartPaymentData
     */
    public function create(Cart $cart, string $paymentUuid, ?string $goPayBankSwift): CartPaymentData
    {
        $domainId = $this->domain->getId();
        $payment = $this->paymentFacade->getEnabledOnDomainByUuid($paymentUuid, $domainId);
        $watchedPriceWithVat = $this->getPaymentWatchedPriceWithVat($domainId, $cart, $payment);

        $cartPaymentData = new CartPaymentData();
        $cartPaymentData->payment = $payment;
        $cartPaymentData->watchedPrice = $watchedPriceWithVat;
        $cartPaymentData->goPayBankSwift = $goPayBankSwift;

        return $cartPaymentData;
    }

    /**
     * @param int $domainId
     * @param \App\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    private function getPaymentWatchedPriceWithVat(int $domainId, Cart $cart, Payment $payment): Money
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            $cart->getTransport(),
            $payment,
            $customerUser,
            null,
            null,
            $cart->getFirstAppliedPromoCode(),
        );

        $watchedPrice = $this->paymentPriceCalculation->calculatePrice(
            $payment,
            $currency,
            $orderPreview->getProductsPrice(),
            $domainId,
        );

        return $watchedPrice->getPriceWithVat();
    }
}
