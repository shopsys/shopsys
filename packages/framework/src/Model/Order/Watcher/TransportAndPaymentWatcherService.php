<?php

namespace Shopsys\FrameworkBundle\Model\Order\Watcher;

use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TransportAndPaymentWatcherService
{
    const SESSION_ROOT = 'transport_and_payment_watcher';
    const SESSION_TRANSPORT_PRICES = 'transport_prices';
    const SESSION_PAYMENT_PRICES = 'payment_prices';

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation
     */
    private $paymentPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation
     */
    private $transportPriceCalculation;

    public function __construct(
        SessionInterface $session,
        PaymentPriceCalculation $paymentPriceCalculation,
        TransportPriceCalculation $transportPriceCalculation
    ) {
        $this->session = $session;
        $this->paymentPriceCalculation = $paymentPriceCalculation;
        $this->transportPriceCalculation = $transportPriceCalculation;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     */
    public function checkTransportAndPayment(OrderData $orderData, OrderPreview $orderPreview, $transports, $payments): \Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentCheckResult
    {
        $transport = $orderData->transport;
        $payment = $orderData->payment;

        $transportPriceChanged = false;
        if ($transport !== null) {
            $transportPriceChanged = $this->checkTransportPrice(
                $transport,
                $orderData->currency,
                $orderPreview,
                $orderData->domainId
            );
        }

        $paymentPriceChanged = false;
        if ($payment !== null) {
            $paymentPriceChanged = $this->checkPaymentPrice(
                $payment,
                $orderData->currency,
                $orderPreview,
                $orderData->domainId
            );
        }

        $this->rememberTransportAndPayment(
            $transports,
            $payments,
            $orderData->currency,
            $orderPreview,
            $orderData->domainId
        );

        return new TransportAndPaymentCheckResult($transportPriceChanged, $paymentPriceChanged);
    }

    private function checkTransportPrice(
        Transport $transport,
        Currency $currency,
        OrderPreview $orderPreview,
        int $domainId
    ): bool {
        $transportPrices = $this->getRememberedTransportPrices();

        if (array_key_exists($transport->getId(), $transportPrices)) {
            $rememberedTransportPriceValue = $transportPrices[$transport->getId()];
            $transportPrice = $this->transportPriceCalculation->calculatePrice(
                $transport,
                $currency,
                $orderPreview->getProductsPrice(),
                $domainId
            );

            if ($rememberedTransportPriceValue != $transportPrice->getPriceWithVat()) {
                return true;
            }
        }

        return false;
    }

    private function checkPaymentPrice(
        Payment $payment,
        Currency $currency,
        OrderPreview $orderPreview,
        int $domainId
    ): bool {
        $paymentPrices = $this->getRememberedPaymentPrices();

        if (array_key_exists($payment->getId(), $paymentPrices)) {
            $rememberedPaymentPriceValue = $paymentPrices[$payment->getId()];
            $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
                $payment,
                $currency,
                $orderPreview->getProductsPrice(),
                $domainId
            );

            if ($rememberedPaymentPriceValue !== $paymentPrice->getPriceWithVat()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @param int $domainId
     */
    private function getTransportPrices(
        $transports,
        Currency $currency,
        OrderPreview $orderPreview,
        $domainId
    ): array {
        $transportPriceValues = [];
        foreach ($transports as $transport) {
            $transportPrice = $this->transportPriceCalculation->calculatePrice(
                $transport,
                $currency,
                $orderPreview->getProductsPrice(),
                $domainId
            );
            $transportPriceValues[$transport->getId()] = $transportPrice->getPriceWithVat();
        }

        return $transportPriceValues;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     * @param int $domainId
     */
    private function getPaymentPrices(
        $payments,
        Currency $currency,
        OrderPreview $orderPreview,
        $domainId
    ): array {
        $paymentPriceValues = [];
        foreach ($payments as $payment) {
            $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
                $payment,
                $currency,
                $orderPreview->getProductsPrice(),
                $domainId
            );
            $paymentPriceValues[$payment->getId()] = $paymentPrice->getPriceWithVat();
        }

        return $paymentPriceValues;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     */
    private function rememberTransportAndPayment(
        array $transports,
        array $payments,
        Currency $currency,
        OrderPreview $orderPreview,
        int $domainId
    ): void {
        $this->session->set(self::SESSION_ROOT, [
            self::SESSION_TRANSPORT_PRICES => $this->getTransportPrices(
                $transports,
                $currency,
                $orderPreview,
                $domainId
            ),
            self::SESSION_PAYMENT_PRICES => $this->getPaymentPrices(
                $payments,
                $currency,
                $orderPreview,
                $domainId
            ),
        ]);
    }

    private function getRememberedTransportAndPayment()
    {
        return $this->session->get(self::SESSION_ROOT, [
            self::SESSION_TRANSPORT_PRICES => [],
            self::SESSION_PAYMENT_PRICES => [],
        ]);
    }

    private function getRememberedTransportPrices()
    {
        return $this->getRememberedTransportAndPayment()[self::SESSION_TRANSPORT_PRICES];
    }

    private function getRememberedPaymentPrices()
    {
        return $this->getRememberedTransportAndPayment()[self::SESSION_PAYMENT_PRICES];
    }
}
