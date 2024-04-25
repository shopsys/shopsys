<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartPriceProvider;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;

class CartPaymentDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartPriceProvider $cartPriceProvider
     */
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly Domain $domain,
        protected readonly CartPriceProvider $cartPriceProvider,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param string $paymentUuid
     * @param string|null $goPayBankSwift
     * @return \Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentData
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
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getPaymentWatchedPriceWithVat(int $domainId, Cart $cart, Payment $payment): Money
    {
        return $this->cartPriceProvider->getPaymentPrice(
            $cart,
            $payment,
            $this->domain->getDomainConfigById($domainId),
        )->getPriceWithVat();
    }
}
