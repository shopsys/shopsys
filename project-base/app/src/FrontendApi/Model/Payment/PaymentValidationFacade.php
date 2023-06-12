<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Payment;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Payment\Exception\InvalidPaymentTransportCombinationException;
use App\FrontendApi\Model\Payment\Exception\PaymentPriceChangedException;
use App\Model\Cart\Cart;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class PaymentValidationFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        private Domain $domain,
        private CurrencyFacade $currencyFacade,
        private OrderPreviewFactory $orderPreviewFactory,
        private CurrentCustomerUser $currentCustomerUser,
        private CartFacade $cartFacade,
    ) {
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @param \App\Model\Cart\Cart $cart
     */
    public function checkPaymentPrice(Payment $payment, Cart $cart): void
    {
        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        /** @var \App\Model\Customer\User\CustomerUser $currentCustomerUser */
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            $cart->getTransport(),
            $payment,
            $currentCustomerUser,
            null,
            null,
            $cart->getFirstAppliedPromoCode(),
        );

        $calculatedPaymentPrice = $orderPreview->getPaymentPrice();

        $paymentWatchedPrice = $cart->getPaymentWatchedPrice();
        if ($paymentWatchedPrice === null || ($calculatedPaymentPrice !== null && !$calculatedPaymentPrice->getPriceWithVat()->equals($paymentWatchedPrice))) {
            throw new PaymentPriceChangedException($calculatedPaymentPrice);
        }
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @param string|null $cartUuid
     */
    public function checkPaymentTransportRelation(Payment $payment, ?string $cartUuid): void
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        $transport = $cart->getTransport();
        if ($transport === null || in_array($transport, $payment->getTransports(), true)) {
            return;
        }

        throw new InvalidPaymentTransportCombinationException();
    }
}
