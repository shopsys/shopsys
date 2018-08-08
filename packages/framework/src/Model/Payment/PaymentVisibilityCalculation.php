<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation;

class PaymentVisibilityCalculation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation
     */
    private $independentPaymentVisibilityCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation
     */
    private $independentTransportVisibilityCalculation;

    public function __construct(
        IndependentPaymentVisibilityCalculation $independentPaymentVisibilityCalculation,
        IndependentTransportVisibilityCalculation $independentTransportVisibilityCalculation
    ) {
        $this->independentPaymentVisibilityCalculation = $independentPaymentVisibilityCalculation;
        $this->independentTransportVisibilityCalculation = $independentTransportVisibilityCalculation;
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

    private function isVisible(Payment $payment, int $domainId): bool
    {
        if (!$this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, $domainId)) {
            return false;
        }

        return $this->hasIndependentlyVisibleTransport($payment, $domainId);
    }

    private function hasIndependentlyVisibleTransport(Payment $payment, int $domainId): bool
    {
        foreach ($payment->getTransports() as $transport) {
            /* @var $transport \Shopsys\FrameworkBundle\Model\Transport\Transport */
            if ($this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, $domainId)) {
                return true;
            }
        }

        return false;
    }
}
