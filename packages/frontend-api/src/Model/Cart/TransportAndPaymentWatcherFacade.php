<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Store\Exception\StoreByUuidNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;
use Shopsys\FrontendApiBundle\Model\Cart\Payment\CartPaymentFacade;
use Shopsys\FrontendApiBundle\Model\Cart\Transport\CartTransportFacade;
use Shopsys\FrontendApiBundle\Model\Payment\Exception\PaymentPriceChangedException;
use Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\TransportPriceChangedException;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\TransportWeightLimitExceededException;
use Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade;

class TransportAndPaymentWatcherFacade
{
    protected CartWithModificationsResult $cartWithModificationsResult;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade
     * @param \Shopsys\FrontendApiBundle\Model\Cart\Transport\CartTransportFacade $cartTransportFacade
     * @param \Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade $transportValidationFacade
     * @param \Shopsys\FrontendApiBundle\Model\Cart\Payment\CartPaymentFacade $cartPaymentFacade
     * @param \Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade $paymentValidationFacade
     */
    public function __construct(
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly TransportFacade $transportFacade,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly OrderPreviewFactory $orderPreviewFactory,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade,
        protected readonly CartTransportFacade $cartTransportFacade,
        protected readonly TransportValidationFacade $transportValidationFacade,
        protected readonly CartPaymentFacade $cartPaymentFacade,
        protected readonly PaymentValidationFacade $paymentValidationFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult $cartWithModificationsResult
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult
     */
    public function checkTransportAndPayment(
        CartWithModificationsResult $cartWithModificationsResult,
        Cart $cart,
    ): CartWithModificationsResult {
        $this->cartWithModificationsResult = $cartWithModificationsResult;

        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
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
        $this->cartWithModificationsResult->setRoundingPrice($orderPreview->getRoundingPrice());

        $this->checkTransport($cart);
        $this->checkPayment($cart);

        return $this->cartWithModificationsResult;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkTransportPrice(Transport $transport, Cart $cart): void
    {
        try {
            $this->transportValidationFacade->checkTransportPrice($transport, $cart);
        } catch (TransportPriceChangedException $exception) {
            $this->cartWithModificationsResult->setTransportPriceChanged(true);
            $this->cartTransportFacade->setTransportWatchedPrice($cart, $exception->getCurrentTransportPrice()->getPriceWithVat());
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     */
    protected function checkPaymentPrice(Cart $cart, Payment $payment): void
    {
        try {
            $this->paymentValidationFacade->checkPaymentPrice($payment, $cart);
        } catch (PaymentPriceChangedException $exception) {
            $this->cartWithModificationsResult->setPaymentPriceChanged(true);
            $this->cartPaymentFacade->setPaymentWatchedPrice($cart, $exception->getCurrentPaymentPrice()->getPriceWithVat());
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkTransportWeightLimit(Transport $transport, Cart $cart): void
    {
        try {
            $this->transportValidationFacade->checkTransportWeightLimit($transport, $cart);
        } catch (TransportWeightLimitExceededException) {
            $this->cartWithModificationsResult->setTransportWeightLimitExceeded(true);
            $this->cartTransportFacade->unsetCartTransport($cart);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkPersonalPickupStoreAvailability(Transport $transport, Cart $cart): void
    {
        try {
            $this->transportValidationFacade->checkPersonalPickupStoreAvailability($transport, $cart->getPickupPlaceIdentifier());
        } catch (StoreByUuidNotFoundException) {
            $this->cartWithModificationsResult->setPersonalPickupStoreUnavailable(true);
            $this->cartTransportFacade->unsetPickupPlaceIdentifierFromCart($cart);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkTransport(Cart $cart): void
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
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkPayment(Cart $cart): void
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
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function setTransportInCartUnavailable(Cart $cart): void
    {
        $this->cartWithModificationsResult->setTransportIsUnavailable();
        $this->cartTransportFacade->unsetCartTransport($cart);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function setPaymentInCartUnavailable(Cart $cart): void
    {
        $this->cartWithModificationsResult->setPaymentIsUnavailable();
        $this->cartPaymentFacade->unsetCartPayment($cart);
    }
}
