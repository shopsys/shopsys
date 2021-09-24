<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\FrontendApi\Model\Payment\PaymentInputData;
use App\FrontendApi\Model\Transport\TransportInputData;
use App\Model\Cart\Cart;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Payment\Payment;
use App\Model\Payment\PaymentFacade;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportFacade;
use Shopsys\Cdn\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

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
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        PaymentPriceCalculation $paymentPriceCalculation,
        TransportPriceCalculation $transportPriceCalculation,
        CurrencyFacade $currencyFacade,
        TransportFacade $transportFacade,
        PaymentFacade $paymentFacade,
        OrderPreviewFactory $orderPreviewFactory,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser
    ) {
        $this->paymentPriceCalculation = $paymentPriceCalculation;
        $this->transportPriceCalculation = $transportPriceCalculation;
        $this->currencyFacade = $currencyFacade;
        $this->transportFacade = $transportFacade;
        $this->paymentFacade = $paymentFacade;
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @param \App\FrontendApi\Model\Cart\CartWithModificationsResult $cartWithModificationsResult
     * @param \App\Model\Cart\Cart $cart
     * @param \App\FrontendApi\Model\Transport\TransportInputData|null $transportInputData
     * @param \App\FrontendApi\Model\Payment\PaymentInputData|null $paymentInputData
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    public function checkTransportAndPayment(
        CartWithModificationsResult $cartWithModificationsResult,
        Cart $cart,
        ?TransportInputData $transportInputData = null,
        ?PaymentInputData $paymentInputData = null
    ): CartWithModificationsResult {
        $this->cartWithModificationsResult = $cartWithModificationsResult;
        if ($transportInputData === null && $paymentInputData === null) {
            return $this->cartWithModificationsResult;
        }
        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $transport = null;
        $payment = null;

        if ($transportInputData) {
            try {
                $transport = $this->transportFacade->getEnabledOnDomainByUuid(
                    $transportInputData->getUuid(),
                    $domainId
                );
            } catch (TransportNotFoundException $exception) {
                $this->cartWithModificationsResult->setTransportIsUnavailable();
            }
        }

        if ($paymentInputData) {
            try {
                $payment = $this->paymentFacade->getEnabledOnDomainByUuid(
                    $paymentInputData->getUuid(),
                    $domainId
                );
            } catch (PaymentNotFoundException $exception) {
                $this->cartWithModificationsResult->setPaymentIsUnavailable();
            }
        }

        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            $transport,
            $payment,
            $customerUser
        );
        if ($transport !== null) {
            $this->checkTransportPrice($transport, $transportInputData, $orderPreview->getProductsPrice(), $currency);
            $this->checkTransportWeightLimit($transport, $cart->getTotalWeight());
        }

        if ($payment !== null) {
            $this->checkPaymentPrice($payment, $paymentInputData, $orderPreview->getProductsPrice(), $currency);
        }

        return $this->cartWithModificationsResult;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\FrontendApi\Model\Transport\TransportInputData $transportInputData
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     */
    private function checkTransportPrice(Transport $transport, TransportInputData $transportInputData, Price $productsPrice, Currency $currency): void
    {
        $domainId = $this->domain->getId();
        $selectedTransportPrice = $transportInputData->getPrice();

        $calculatedTransportPrice = $this->transportPriceCalculation->calculatePrice(
            $transport,
            $currency,
            $productsPrice,
            $domainId
        );

        if ($calculatedTransportPrice->getPriceWithVat()->equals($selectedTransportPrice->getPriceWithVat())) {
            return;
        }

        $this->cartWithModificationsResult->setTransportPriceChanged($transport);
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

        if ($calculatedPaymentPrice->getPriceWithVat()->equals($selectedPaymentPrice->getPriceWithVat())) {
            return;
        }
        $this->cartWithModificationsResult->setPaymentPriceChanged($payment);
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param int $cartTotalWeight
     */
    private function checkTransportWeightLimit(Transport $transport, int $cartTotalWeight): void
    {
        $transportWeightLimitExceeded = $transport->getMaxWeight() < $cartTotalWeight;
        $this->cartWithModificationsResult->setTransportWeightLimitExceeded($transportWeightLimitExceeded);
    }
}
