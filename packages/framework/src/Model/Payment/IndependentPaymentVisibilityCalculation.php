<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleProvider;

class IndependentPaymentVisibilityCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleProvider $customerUserRoleProvider
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CustomerUserRoleProvider $customerUserRoleProvider,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param int $domainId
     * @return bool
     */
    public function isIndependentlyVisible(Payment $payment, $domainId)
    {
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

        /** @var string|null $paymentName */
        $paymentName = $payment->getName($locale);

        if ($paymentName === '' || $paymentName === null) {
            return false;
        }

        if ($payment->isHidden() || $payment->isDeleted() || $payment->isHiddenByGoPayByDomainId($domainId)) {
            return false;
        }

        if (!$payment->isEnabled($domainId)) {
            return false;
        }

        if (!$this->customerUserRoleProvider->canCurrentCustomerUserSeePrices()) {
            return !$payment->isGatewayPayment();
        }

        return true;
    }
}
