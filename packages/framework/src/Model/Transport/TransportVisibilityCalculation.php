<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation;

class TransportVisibilityCalculation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation
     */
    private $independentTransportVisibilityCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation
     */
    private $independentPaymentVisibilityCalculation;

    public function __construct(
        IndependentTransportVisibilityCalculation $independentTransportVisibilityCalculation,
        IndependentPaymentVisibilityCalculation $independentPaymentVisibilityCalculation
    ) {
        $this->independentTransportVisibilityCalculation = $independentTransportVisibilityCalculation;
        $this->independentPaymentVisibilityCalculation = $independentPaymentVisibilityCalculation;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $allPaymentsOnDomain
     */
    public function isVisible(Transport $transport, array $allPaymentsOnDomain, int $domainId): bool
    {
        if (!$this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, $domainId)) {
            return false;
        }

        return $this->existsIndependentlyVisiblePaymentWithTransport($allPaymentsOnDomain, $transport, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     */
    private function existsIndependentlyVisiblePaymentWithTransport(array $payments, Transport $transport, int $domainId): bool
    {
        foreach ($payments as $payment) {
            if ($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, $domainId)) {
                if ($payment->getTransports()->contains($transport)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $visiblePaymentsOnDomain
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function filterVisible(array $transports, array $visiblePaymentsOnDomain, int $domainId): array
    {
        $visibleTransports = [];

        foreach ($transports as $transport) {
            if ($this->isVisible($transport, $visiblePaymentsOnDomain, $domainId)) {
                $visibleTransports[] = $transport;
            }
        }

        return $visibleTransports;
    }
}
