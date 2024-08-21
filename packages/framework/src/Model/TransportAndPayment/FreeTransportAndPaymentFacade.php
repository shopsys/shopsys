<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\TransportAndPayment;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class FreeTransportAndPaymentFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver $customerUserRoleResolver
     */
    public function __construct(
        protected readonly PricingSetting $pricingSetting,
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
    ) {
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isActive($domainId)
    {
        if (!$this->customerUserRoleResolver->canCurrentCustomerUserSeePrices()) {
            return false;
        }

        return $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId) !== null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $productsPriceWithVat
     * @param int $domainId
     * @return bool
     */
    protected function isFree(Money $productsPriceWithVat, $domainId)
    {
        $freeTransportAndPaymentPriceLimit = $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId);

        if ($freeTransportAndPaymentPriceLimit === null) {
            return false;
        }

        return $productsPriceWithVat->isGreaterThanOrEqualTo($freeTransportAndPaymentPriceLimit);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $productsPriceWithVat
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getRemainingPriceWithVat(Money $productsPriceWithVat, $domainId): Money
    {
        if (!$this->isFree($productsPriceWithVat, $domainId) && $this->isActive($domainId)) {
            return $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId)->subtract($productsPriceWithVat);
        }

        return Money::zero();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    protected function getFreeTransportAndPaymentPriceLimitOnDomain($domainId): ?Money
    {
        return $this->pricingSetting->getFreeTransportAndPaymentPriceLimit($domainId);
    }
}
