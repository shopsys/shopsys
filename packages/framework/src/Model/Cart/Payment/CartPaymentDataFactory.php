<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceProvider;

class CartPaymentDataFactory
{
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly Domain $domain,
        protected readonly PaymentPriceProvider $paymentPriceProvider,
    ) {
    }

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

    protected function getPaymentWatchedPriceWithVat(int $domainId, Cart $cart, Payment $payment): Money
    {
        return $this->paymentPriceProvider->getPaymentPrice(
            $cart,
            $payment,
            $this->domain->getDomainConfigById($domainId),
        )->getPriceWithVat();
    }
}
