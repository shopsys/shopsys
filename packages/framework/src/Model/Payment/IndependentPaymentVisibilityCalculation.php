<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class IndependentPaymentVisibilityCalculation
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param int $domainId
     * @return bool
     */
    public function isIndependentlyVisible(Payment $payment, $domainId)
    {
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

        $paymentName = $payment->getName($locale);

        if ($paymentName === '' || $paymentName === null) {
            return false;
        }

        if ($payment->isHidden() || $payment->isDeleted()) {
            return false;
        }

        return $payment->isEnabled($domainId);
    }
}
