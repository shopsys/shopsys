<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation as BaseIndependentPaymentVisibilityCalculation;

/**
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
 * @method bool isIndependentlyVisible(\App\Model\Payment\Payment $payment, int $domainId)
 */
class IndependentPaymentVisibilityCalculation extends BaseIndependentPaymentVisibilityCalculation
{
}
