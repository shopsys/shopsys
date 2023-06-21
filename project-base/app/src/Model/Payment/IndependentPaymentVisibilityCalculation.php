<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation as BaseIndependentPaymentVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Payment;

/**
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
 */
class IndependentPaymentVisibilityCalculation extends BaseIndependentPaymentVisibilityCalculation
{
    /**
     * @param \App\Model\Payment\Payment $payment
     * @param int $domainId
     * @return bool
     */
    public function isIndependentlyVisible(Payment $payment, $domainId): bool
    {
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

        /** @var string|null $paymentName */
        $paymentName = $payment->getName($locale);

        if ($paymentName === '' || $paymentName === null) {
            return false;
        }

        if ($payment->isHidden() || $payment->isHiddenByGoPay()) {
            return false;
        }

        return $payment->isEnabled($domainId);
    }
}
