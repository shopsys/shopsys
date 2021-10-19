<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\FrontendApi\Model\Payment\PaymentInputData;
use App\FrontendApi\Model\Transport\TransportInputData;
use App\Model\Cart\Cart;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Order\PromoCode\PromoCode;
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
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
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
        FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade
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
    }

    /**
     * @param \App\FrontendApi\Model\Cart\CartWithModificationsResult $cartWithModificationsResult
     * @param \App\Model\Cart\Cart $cart
     * @param \App\FrontendApi\Model\Transport\TransportInputData|null $transportInputData
     * @param \App\FrontendApi\Model\Payment\PaymentInputData|null $paymentInputData
     * @param \App\Model\Order\PromoCode\PromoCode|null $promoCode
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    public function checkTransportAndPayment(
        CartWithModificationsResult $cartWithModificationsResult,
        Cart $cart,
        ?TransportInputData $transportInputData = null,
        ?PaymentInputData $paymentInputData = null,
        ?PromoCode $promoCode = null
    ): CartWithModificationsResult {
        $this->cartWithModificationsResult = $cartWithModificationsResult;

        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $transport = null;
        $payment = null;

        if ($transportInputData !== null) {
            $transport = $this->getTransportFromInputData($transportInputData);

            $this->checkPersonalPickupStoreAvailability($transportInputData);
        }

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
            $promoCode
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
        $this->cartWithModificationsResult->setTransport($transport);
        $this->cartWithModificationsResult->setPayment($payment);

        if ($transport !== null) {
            $this->checkTransportPrice($transport, $transportInputData, $orderPreview->getProductsPrice(), $currency);
            $transportWeightLimitExceeded = $this->checkTransportWeightLimit($transport, $cart->getTotalWeight());
            if ($transportWeightLimitExceeded) {
                $this->cartWithModificationsResult->setTransport(null);
            }
        }

        if ($payment !== null) {
            $this->checkPaymentPrice($payment, $paymentInputData, $orderPreview->getProductsPrice(), $currency);
        }

        return $this->cartWithModificationsResult;
    }

    /**
     * @param \App\FrontendApi\Model\Transport\TransportInputData $transportInputData
     * @return \App\Model\Transport\Transport|null
     */
    private function getTransportFromInputData(TransportInputData $transportInputData): ?Transport
    {
        try {
            return $this->transportFacade->getEnabledOnDomainByUuid(
                $transportInputData->getUuid(),
                $this->domain->getId()
            );
        } catch (TransportNotFoundException $exception) {
            $this->cartWithModificationsResult->setTransportIsUnavailable();
        }

        return null;
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

        $this->cartWithModificationsResult->setTransportPriceChanged(
            !$calculatedTransportPrice->getPriceWithVat()->equals($selectedTransportPrice->getPriceWithVat())
        );
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
     * @param int $cartTotalWeight
     * @return bool
     */
    private function checkTransportWeightLimit(Transport $transport, int $cartTotalWeight): bool
    {
        $transportWeightLimitExceeded = $transport->getMaxWeight() !== null && $transport->getMaxWeight() < $cartTotalWeight;
        $this->cartWithModificationsResult->setTransportWeightLimitExceeded($transportWeightLimitExceeded);

        return $transportWeightLimitExceeded;
    }

    /**
     * @param \App\FrontendApi\Model\Transport\TransportInputData $transportInputData
     */
    private function checkPersonalPickupStoreAvailability(TransportInputData $transportInputData): void
    {
        if ($transportInputData->getPersonalPickupStoreUuid() === null) {
            return;
        }

        try {
            $this->storeFacade->getByUuidEnabledOnDomain(
                $transportInputData->getPersonalPickupStoreUuid(),
                $this->domain->getId()
            );
        } catch (StoreByUuidNotFoundException $e) {
            $this->cartWithModificationsResult->setPersonalPickupStoreUnavailable(true);
        }
    }
}
