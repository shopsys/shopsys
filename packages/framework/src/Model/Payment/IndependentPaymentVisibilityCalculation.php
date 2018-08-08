<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class IndependentPaymentVisibilityCalculation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentRepository
     */
    private $paymentRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(
        PaymentRepository $paymentRepository,
        Domain $domain
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->domain = $domain;
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isIndependentlyVisible(Payment $payment, $domainId)
    {
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

        if (strlen($payment->getName($locale)) === 0) {
            return false;
        }

        if ($payment->isHidden()) {
            return false;
        }

        if (!$payment->isEnabled($domainId)) {
            return false;
        }

        return true;
    }
}
