<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceProvider;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

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
        $watchedPrice = $this->getPaymentWatchedPrice($domainId, $cart, $payment);

        $cartPaymentData = new CartPaymentData();
        $cartPaymentData->payment = $payment;
        $cartPaymentData->watchedPrice = $watchedPrice->getPriceWithVat();
        $cartPaymentData->watchedPriceWithoutVat = $watchedPrice->getPriceWithoutVat();
        $cartPaymentData->goPayBankSwift = $goPayBankSwift;

        return $cartPaymentData;
    }

    protected function getPaymentWatchedPrice(int $domainId, Cart $cart, Payment $payment): PriceInterface
    {
        return $this->paymentPriceProvider->getPaymentPrice(
            $cart,
            $payment,
            $this->domain->getDomainConfigById($domainId),
        );
    }
}
