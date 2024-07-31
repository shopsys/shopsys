<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceProvider;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Payment\Exception\InvalidPaymentTransportCombinationException;
use Shopsys\FrontendApiBundle\Model\Payment\Exception\PaymentPriceChangedException;

class PaymentValidationFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceProvider $paymentPriceProvider
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly PaymentPriceProvider $paymentPriceProvider,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     * @throws \Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException
     * @throws \Shopsys\FrontendApiBundle\Model\Payment\Exception\PaymentPriceChangedException
     */
    public function checkPaymentPrice(Payment $payment, Order $cart): void
    {
        $calculatedPaymentPrice = $this->paymentPriceProvider->getPaymentPrice(
            $cart,
            $payment,
            $this->domain->getCurrentDomainConfig(),
        );

        $paymentWatchedPrice = $cart->getPaymentWatchedPrice();

        if ($paymentWatchedPrice === null || !$calculatedPaymentPrice->getPriceWithVat()->equals($paymentWatchedPrice)) {
            throw new PaymentPriceChangedException($calculatedPaymentPrice);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param string|null $cartUuid
     */
    public function checkPaymentTransportRelation(Payment $payment, ?string $cartUuid): void
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        $transport = $cart->getTransport();

        if ($transport === null || in_array($transport, $payment->getTransports(), true)) {
            return;
        }

        throw new InvalidPaymentTransportCombinationException();
    }
}
