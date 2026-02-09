<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation;

class PaymentVisibilityCalculation
{
    public function __construct(
        protected readonly IndependentPaymentVisibilityCalculation $independentPaymentVisibilityCalculation,
        protected readonly IndependentTransportVisibilityCalculation $independentTransportVisibilityCalculation,
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

        return $this->hasIndependentlyVisibleTransport($payment, $domainId);
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
