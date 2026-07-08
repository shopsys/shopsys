<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation;

class PaymentVisibilityCalculation
{
    public function __construct(
        protected readonly IndependentPaymentVisibilityCalculation $independentPaymentVisibilityCalculation,
        protected readonly IndependentTransportVisibilityCalculation $independentTransportVisibilityCalculation,
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function filterVisible(array $payments, int $domainId): array
    {
        $visiblePayments = [];

        foreach ($payments as $payment) {
            if ($this->isVisible($payment, $domainId)) {
                $visiblePayments[] = $payment;
            }
        }

        return $visiblePayments;
    }

    public function isVisible(Payment $payment, int $domainId): bool
    {
        if (!$this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, $domainId)) {
            return false;
        }

        if (!$this->isGoPayPaymentMethodMatchingCurrentCurrency($payment, $domainId)) {
            return false;
        }

        return $this->hasIndependentlyVisibleTransport($payment, $domainId);
    }

    /**
     * The GoPay payment methods are bound to a single currency, the payment is hidden when another currency is selected
     */
    protected function isGoPayPaymentMethodMatchingCurrentCurrency(Payment $payment, int $domainId): bool
    {
        $goPayPaymentMethod = $payment->getGoPayPaymentMethodByDomainId($domainId);

        if ($goPayPaymentMethod === null) {
            return true;
        }

        return $goPayPaymentMethod->getCurrency()->getCode() === $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($domainId)->getCode();
    }

    protected function hasIndependentlyVisibleTransport(Payment $payment, int $domainId): bool
    {
        foreach ($payment->getTransports() as $transport) {
            if ($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, $domainId)) {
                return true;
            }
        }

        return false;
    }
}
