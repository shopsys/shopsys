<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation;

class TransportVisibilityCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation $independentTransportVisibilityCalculation
     * @param \Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation $independentPaymentVisibilityCalculation
     */
    public function __construct(
        protected readonly IndependentTransportVisibilityCalculation $independentTransportVisibilityCalculation,
        protected readonly IndependentPaymentVisibilityCalculation $independentPaymentVisibilityCalculation
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $allPaymentsOnDomain
     * @param int $domainId
     * @return bool
     */
    public function isVisible(Transport $transport, array $allPaymentsOnDomain, $domainId)
    {
        if (!$this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, $domainId)) {
            return false;
        }

        return $this->existsIndependentlyVisiblePaymentWithTransport($allPaymentsOnDomain, $transport, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param int $domainId
     * @return bool
     */
    protected function existsIndependentlyVisiblePaymentWithTransport(array $payments, Transport $transport, $domainId)
    {
        foreach ($payments as $payment) {
            if ($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, $domainId)) {
                if (in_array($transport, $payment->getTransports(), true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $visiblePaymentsOnDomain
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function filterVisible(array $transports, array $visiblePaymentsOnDomain, $domainId)
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
