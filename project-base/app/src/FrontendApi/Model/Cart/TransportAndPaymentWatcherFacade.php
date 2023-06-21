<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\FrontendApi\Model\Payment\Exception\PaymentPriceChangedException;
use App\FrontendApi\Model\Payment\PaymentValidationFacade;
use App\FrontendApi\Model\Transport\Exception\TransportPriceChangedException;
use App\FrontendApi\Model\Transport\Exception\TransportWeightLimitExceededException;
use App\FrontendApi\Model\Transport\TransportValidationFacade;
use App\Model\Cart\Cart;
use App\Model\Cart\Payment\CartPaymentFacade;
use App\Model\Cart\Transport\CartTransportFacade;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Payment\Payment;
use App\Model\Payment\PaymentFacade;
use App\Model\Store\Exception\StoreByUuidNotFoundException;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;

class TransportAndPaymentWatcherFacade
{
    private CartWithModificationsResult $cartWithModificationsResult;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade
     * @param \App\Model\Cart\Transport\CartTransportFacade $cartTransportFacade
     * @param \App\FrontendApi\Model\Transport\TransportValidationFacade $transportValidationFacade
     * @param \App\Model\Cart\Payment\CartPaymentFacade $cartPaymentFacade
     * @param \App\FrontendApi\Model\Payment\PaymentValidationFacade $paymentValidationFacade
     */
    public function __construct(
        private CurrencyFacade $currencyFacade,
        private TransportFacade $transportFacade,
        private PaymentFacade $paymentFacade,
        private OrderPreviewFactory $orderPreviewFactory,
        private Domain $domain,
        private CurrentCustomerUser $currentCustomerUser,
        private FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade,
        private CartTransportFacade $cartTransportFacade,
        private TransportValidationFacade $transportValidationFacade,
        private CartPaymentFacade $cartPaymentFacade,
        private PaymentValidationFacade $paymentValidationFacade,
    ) {
    }

    /**
     * @param \App\FrontendApi\Model\Cart\CartWithModificationsResult $cartWithModificationsResult
     * @param \App\Model\Cart\Cart $cart
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    public function checkTransportAndPayment(
        CartWithModificationsResult $cartWithModificationsResult,
        Cart $cart,
    ): CartWithModificationsResult {
        $this->cartWithModificationsResult = $cartWithModificationsResult;

        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $transport = $cart->getTransport();
        $payment = $cart->getPayment();

        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            $transport,
            $payment,
            $customerUser,
            null,
            null,
            $cart->getFirstAppliedPromoCode(),
        );

        if ($this->freeTransportAndPaymentFacade->isActive($domainId)) {
            $amountWithVatForFreeTransport = $this->freeTransportAndPaymentFacade->getRemainingPriceWithVat(
                $orderPreview->getProductsPrice()->getPriceWithVat(),
                $domainId,
            );

            $this->cartWithModificationsResult->setRemainingAmountWithVatForFreeTransport($amountWithVatForFreeTransport);
        }

        $this->cartWithModificationsResult->setTotalPrice($orderPreview->getTotalPrice());
        $this->cartWithModificationsResult->setTotalItemsPrice($orderPreview->getProductsPrice());
        $this->cartWithModificationsResult->setTotalDiscountPrice($orderPreview->getTotalPriceDiscount());
        $this->cartWithModificationsResult->setTotalPriceWithoutDiscountTransportAndPayment($orderPreview->getTotalPriceWithoutDiscountTransportAndPayment());

        $this->checkTransport($cart);
        $this->checkPayment($cart);

        return $this->cartWithModificationsResult;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkTransportPrice(Transport $transport, Cart $cart): void
    {
        try {
            $this->transportValidationFacade->checkTransportPrice($transport, $cart);
        } catch (TransportPriceChangedException $exception) {
            $this->cartWithModificationsResult->setTransportPriceChanged(true);
            $this->cartTransportFacade->setTransportWatchedPrice($cart, $exception->getCurrentTransportPrice()->getPriceWithVat());
        }
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param \App\Model\Payment\Payment $payment
     */
    private function checkPaymentPrice(Cart $cart, Payment $payment): void
    {
        try {
            $this->paymentValidationFacade->checkPaymentPrice($payment, $cart);
        } catch (PaymentPriceChangedException $exception) {
            $this->cartWithModificationsResult->setPaymentPriceChanged(true);
            $this->cartPaymentFacade->setPaymentWatchedPrice($cart, $exception->getCurrentPaymentPrice()->getPriceWithVat());
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkTransportWeightLimit(Transport $transport, Cart $cart): void
    {
        try {
            $this->transportValidationFacade->checkTransportWeightLimit($transport, $cart);
        } catch (TransportWeightLimitExceededException $exception) {
            $this->cartWithModificationsResult->setTransportWeightLimitExceeded(true);
            $this->cartTransportFacade->unsetCartTransport($cart);
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkPersonalPickupStoreAvailability(Transport $transport, Cart $cart): void
    {
        try {
            $this->transportValidationFacade->checkPersonalPickupStoreAvailability($transport, $cart->getPickupPlaceIdentifier());
        } catch (StoreByUuidNotFoundException $e) {
            $this->cartWithModificationsResult->setPersonalPickupStoreUnavailable(true);
            $this->cartTransportFacade->unsetPickupPlaceIdentifierFromCart($cart);
        }
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkTransport(Cart $cart): void
    {
        if ($cart->isEmpty()) {
            $this->cartTransportFacade->unsetCartTransport($cart);
        }

        $transport = $cart->getTransport();

        if ($transport === null) {
            if ($cart->getTransportWatchedPrice() !== null) {
                // this might happen when transport is set to null in cart thanks to "onDelete=SET NULL" ORM setting
                $this->setTransportInCartUnavailable($cart);
            }

            return;
        }

        if ($this->transportFacade->isTransportVisibleAndEnabledOnCurrentDomain($transport) === false) {
            $this->setTransportInCartUnavailable($cart);

            return;
        }
        $this->checkTransportPrice($transport, $cart);
        $this->checkTransportWeightLimit($transport, $cart);
        $this->checkPersonalPickupStoreAvailability($transport, $cart);
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkPayment(Cart $cart): void
    {
        if ($cart->isEmpty()) {
            $this->cartPaymentFacade->unsetCartPayment($cart);
        }

        $payment = $cart->getPayment();

        if ($payment === null) {
            if ($cart->getPaymentWatchedPrice() !== null) {
                // this might happen when payment is set to null in cart thanks to "onDelete=SET NULL" ORM setting
                $this->setPaymentInCartUnavailable($cart);
            }

            return;
        }

        if ($this->paymentFacade->isPaymentVisibleAndEnabledOnCurrentDomain($payment) === false) {
            $this->setPaymentInCartUnavailable($cart);

            return;
        }
        $this->checkPaymentPrice($cart, $payment);
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function setTransportInCartUnavailable(Cart $cart): void
    {
        $this->cartWithModificationsResult->setTransportIsUnavailable();
        $this->cartTransportFacade->unsetCartTransport($cart);
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function setPaymentInCartUnavailable(Cart $cart): void
    {
        $this->cartWithModificationsResult->setPaymentIsUnavailable();
        $this->cartPaymentFacade->unsetCartPayment($cart);
    }
}
