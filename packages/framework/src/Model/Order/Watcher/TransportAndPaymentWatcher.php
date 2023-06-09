<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Watcher;

use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Symfony\Component\HttpFoundation\RequestStack;

class TransportAndPaymentWatcher
{
    protected const SESSION_ROOT = 'transport_and_payment_watcher';
    protected const SESSION_TRANSPORT_PRICES = 'transport_prices';
    protected const SESSION_PAYMENT_PRICES = 'payment_prices';

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     */
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     * @return \Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentCheckResult
     */
    public function checkTransportAndPayment(
        OrderData $orderData,
        OrderPreview $orderPreview,
        array $transports,
        array $payments,
    ): TransportAndPaymentCheckResult {
        $transport = $orderData->transport;
        $payment = $orderData->payment;

        $transportPriceChanged = false;
        if ($transport !== null) {
            $transportPriceChanged = $this->checkTransportPrice(
                $transport,
                $orderData->currency,
                $orderPreview,
                $orderData->domainId,
            );
        }

        $paymentPriceChanged = false;
        if ($payment !== null) {
            $paymentPriceChanged = $this->checkPaymentPrice(
                $payment,
                $orderData->currency,
                $orderPreview,
                $orderData->domainId,
            );
        }

        $this->rememberTransportAndPayment(
            $transports,
            $payments,
            $orderData->currency,
            $orderPreview,
            $orderData->domainId,
        );

        return new TransportAndPaymentCheckResult($transportPriceChanged, $paymentPriceChanged);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param int $domainId
     * @return bool
     */
    protected function checkTransportPrice(
        Transport $transport,
        Currency $currency,
        OrderPreview $orderPreview,
        int $domainId,
    ): bool {
        $transportPrices = $this->getRememberedTransportPrices();

        if (array_key_exists($transport->getId(), $transportPrices)) {
            $rememberedTransportPriceValue = $transportPrices[$transport->getId()];

            $transportPrice = $this->transportPriceCalculation->calculatePrice(
                $transport,
                $currency,
                $orderPreview->getProductsPrice(),
                $domainId,
            );

            if (!$transportPrice->getPriceWithVat()->equals($rememberedTransportPriceValue)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param int $domainId
     * @return bool
     */
    protected function checkPaymentPrice(
        Payment $payment,
        Currency $currency,
        OrderPreview $orderPreview,
        int $domainId,
    ): bool {
        $paymentPrices = $this->getRememberedPaymentPrices();

        if (array_key_exists($payment->getId(), $paymentPrices)) {
            $rememberedPaymentPriceValue = $paymentPrices[$payment->getId()];

            $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
                $payment,
                $currency,
                $orderPreview->getProductsPrice(),
                $domainId,
            );

            if (!$paymentPrice->getPriceWithVat()->equals($rememberedPaymentPriceValue)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money[]
     */
    protected function getTransportPrices(
        array $transports,
        Currency $currency,
        OrderPreview $orderPreview,
        int $domainId,
    ): array {
        $transportPriceValues = [];
        foreach ($transports as $transport) {
            $transportPrice = $this->transportPriceCalculation->calculatePrice(
                $transport,
                $currency,
                $orderPreview->getProductsPrice(),
                $domainId,
            );
            $transportPriceValues[$transport->getId()] = $transportPrice->getPriceWithVat();
        }

        return $transportPriceValues;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money[]
     */
    protected function getPaymentPrices(
        array $payments,
        Currency $currency,
        OrderPreview $orderPreview,
        int $domainId,
    ): array {
        $paymentPriceValues = [];
        foreach ($payments as $payment) {
            $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
                $payment,
                $currency,
                $orderPreview->getProductsPrice(),
                $domainId,
            );
            $paymentPriceValues[$payment->getId()] = $paymentPrice->getPriceWithVat();
        }

        return $paymentPriceValues;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param int $domainId
     */
    protected function rememberTransportAndPayment(
        array $transports,
        array $payments,
        Currency $currency,
        OrderPreview $orderPreview,
        int $domainId,
    ): void {
        $this->requestStack->getSession()->set(static::SESSION_ROOT, [
            static::SESSION_TRANSPORT_PRICES => $this->getTransportPrices(
                $transports,
                $currency,
                $orderPreview,
                $domainId,
            ),
            static::SESSION_PAYMENT_PRICES => $this->getPaymentPrices(
                $payments,
                $currency,
                $orderPreview,
                $domainId,
            ),
        ]);
    }

    /**
     * @return array
     */
    protected function getRememberedTransportAndPayment(): array
    {
        return $this->requestStack->getSession()->get(static::SESSION_ROOT, [
            static::SESSION_TRANSPORT_PRICES => [],
            static::SESSION_PAYMENT_PRICES => [],
        ]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money[]
     */
    protected function getRememberedTransportPrices(): array
    {
        return $this->getRememberedTransportAndPayment()[static::SESSION_TRANSPORT_PRICES];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money[]
     */
    protected function getRememberedPaymentPrices(): array
    {
        return $this->getRememberedTransportAndPayment()[static::SESSION_PAYMENT_PRICES];
    }
}
