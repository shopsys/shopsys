<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\FrontendApi\Model\Payment\PaymentInputData;
use App\Model\Cart\Cart;
use App\Model\Cart\Transport\CartTransportFacade;
use App\Model\Order\Preview\OrderPreview;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Payment\Payment;
use App\Model\Payment\PaymentFacade;
use App\Model\Store\Exception\StoreByUuidNotFoundException;
use App\Model\Store\StoreFacade;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportFacade;
use Shopsys\Cdn\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;

class TransportAndPaymentWatcherFacade
{
    /**
     * @var \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    private CartWithModificationsResult $cartWithModificationsResult;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation
     */
    private PaymentPriceCalculation $paymentPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation
     */
    private TransportPriceCalculation $transportPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private CurrencyFacade $currencyFacade;

    /**
     * @var \App\Model\Transport\TransportFacade
     */
    private TransportFacade $transportFacade;

    /**
     * @var \App\Model\Payment\PaymentFacade
     */
    private PaymentFacade $paymentFacade;

    /**
     * @var \App\Model\Order\Preview\OrderPreviewFactory
     */
    private OrderPreviewFactory $orderPreviewFactory;

    /**
     * @var \Shopsys\Cdn\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\Model\Store\StoreFacade
     */
    private StoreFacade $storeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade
     */
    private FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade;

    /**
     * @var \App\Model\Cart\Transport\CartTransportFacade
     */
    private CartTransportFacade $cartTransportFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade
     * @param \App\Model\Cart\Transport\CartTransportFacade $cartTransportFacade
     */
    public function __construct(
        PaymentPriceCalculation $paymentPriceCalculation,
        TransportPriceCalculation $transportPriceCalculation,
        CurrencyFacade $currencyFacade,
        TransportFacade $transportFacade,
        PaymentFacade $paymentFacade,
        OrderPreviewFactory $orderPreviewFactory,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        StoreFacade $storeFacade,
        FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade,
        CartTransportFacade $cartTransportFacade
    ) {
        $this->paymentPriceCalculation = $paymentPriceCalculation;
        $this->transportPriceCalculation = $transportPriceCalculation;
        $this->currencyFacade = $currencyFacade;
        $this->transportFacade = $transportFacade;
        $this->paymentFacade = $paymentFacade;
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->storeFacade = $storeFacade;
        $this->freeTransportAndPaymentFacade = $freeTransportAndPaymentFacade;
        $this->cartTransportFacade = $cartTransportFacade;
    }

    /**
     * @param \App\FrontendApi\Model\Cart\CartWithModificationsResult $cartWithModificationsResult
     * @param \App\Model\Cart\Cart $cart
     * @param \App\FrontendApi\Model\Payment\PaymentInputData|null $paymentInputData
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    public function checkTransportAndPayment(
        CartWithModificationsResult $cartWithModificationsResult,
        Cart $cart,
        ?PaymentInputData $paymentInputData = null
    ): CartWithModificationsResult {
        $this->cartWithModificationsResult = $cartWithModificationsResult;

        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $transport = $cart->getTransport();
        $payment = null;

        if ($paymentInputData) {
            $payment = $this->getPaymentFromInputData($paymentInputData);
        }

        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            $transport,
            $payment,
            $customerUser,
            null,
            null,
            $cart->getFirstAppliedPromoCode()
        );

        if ($this->freeTransportAndPaymentFacade->isActive($domainId)) {
            $amountWithVatForFreeTransport = $this->freeTransportAndPaymentFacade->getRemainingPriceWithVat(
                $orderPreview->getTotalPrice()->getPriceWithVat(),
                $domainId
            );

            $this->cartWithModificationsResult->setRemainingAmountWithVatForFreeTransport($amountWithVatForFreeTransport);
        }

        $this->cartWithModificationsResult->setTotalPrice($orderPreview->getTotalPrice());
        $this->cartWithModificationsResult->setTotalDiscountPrice($orderPreview->getTotalPriceDiscount());
        $this->cartWithModificationsResult->setPayment($payment);

        $this->checkTransport($orderPreview, $cart);

        if ($payment !== null) {
            $this->checkPaymentPrice($payment, $paymentInputData, $orderPreview->getProductsPrice(), $currency);
        }

        return $this->cartWithModificationsResult;
    }

    /**
     * @param \App\FrontendApi\Model\Payment\PaymentInputData $paymentInputData
     * @return \App\Model\Payment\Payment
     */
    private function getPaymentFromInputData(PaymentInputData $paymentInputData): ?Payment
    {
        try {
            return $this->paymentFacade->getEnabledOnDomainByUuid(
                $paymentInputData->getUuid(),
                $this->domain->getId()
            );
        } catch (PaymentNotFoundException $exception) {
            $this->cartWithModificationsResult->setPaymentIsUnavailable();
        }

        return null;
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param \App\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     */
    private function checkTransportPrice(Cart $cart, Transport $transport, Price $productsPrice): void
    {
        $domainId = $this->domain->getId();
        $calculatedTransportPrice = $this->transportPriceCalculation->calculatePrice(
            $transport,
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId),
            $productsPrice,
            $domainId
        );

        $transportWatchedPrice = $cart->getTransportWatchedPrice();
        $transportPriceChanged = $transportWatchedPrice === null || !$calculatedTransportPrice->getPriceWithVat()->equals($transportWatchedPrice);
        if (!$transportPriceChanged) {
            return;
        }

        $this->cartWithModificationsResult->setTransportPriceChanged(true);
        $this->cartTransportFacade->setTransportWatchedPrice($cart, $calculatedTransportPrice->getPriceWithVat());
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @param \App\FrontendApi\Model\Payment\PaymentInputData $paymentInputData
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     */
    private function checkPaymentPrice(Payment $payment, PaymentInputData $paymentInputData, Price $productsPrice, Currency $currency): void
    {
        $domainId = $this->domain->getId();

        $selectedPaymentPrice = $paymentInputData->getPrice();
        $calculatedPaymentPrice = $this->paymentPriceCalculation->calculatePrice(
            $payment,
            $currency,
            $productsPrice,
            $domainId
        );

        $this->cartWithModificationsResult->setPaymentPriceChanged(
            !$calculatedPaymentPrice->getPriceWithVat()->equals($selectedPaymentPrice->getPriceWithVat())
        );
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkTransportWeightLimit(Transport $transport, Cart $cart): void
    {
        $transportWeightLimitExceeded = $transport->getMaxWeight() !== null && $transport->getMaxWeight() < $cart->getTotalWeight();
        $this->cartWithModificationsResult->setTransportWeightLimitExceeded($transportWeightLimitExceeded);
        if ($transportWeightLimitExceeded) {
            $this->cartTransportFacade->unsetCartTransport($cart);
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkPersonalPickupStoreAvailability(Transport $transport, Cart $cart): void
    {
        if ($cart->getPickupPlaceIdentifier() === null || $transport->isPacketery()) {
            return;
        }

        try {
            $this->storeFacade->getByUuidEnabledOnDomain(
                $cart->getPickupPlaceIdentifier(),
                $this->domain->getId()
            );
        } catch (StoreByUuidNotFoundException $e) {
            $this->cartWithModificationsResult->setPersonalPickupStoreUnavailable(true);
        }
    }

    /**
     * @param \App\Model\Order\Preview\OrderPreview $orderPreview
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkTransport(
        OrderPreview $orderPreview,
        Cart $cart
    ): void {
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
        $this->checkTransportPrice($cart, $transport, $orderPreview->getProductsPrice());
        $this->checkTransportWeightLimit($transport, $cart);
        $this->checkPersonalPickupStoreAvailability($transport, $cart);
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function setTransportInCartUnavailable(Cart $cart): void
    {
        $this->cartWithModificationsResult->setTransportIsUnavailable();
        $this->cartTransportFacade->unsetCartTransport($cart);
    }
}
