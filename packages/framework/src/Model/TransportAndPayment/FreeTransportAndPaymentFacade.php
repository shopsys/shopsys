<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\TransportAndPayment;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
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
     * @param bool $forceFreeTransportAndPayment
     * @return bool
     */
    public function isActive(int $domainId, bool $forceFreeTransportAndPayment): bool
    {
        if (!$this->customerUserRoleResolver->canCurrentCustomerUserSeePrices()) {
            return false;
        }

        if ($forceFreeTransportAndPayment) {
            return true;
        }

        return $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId) !== null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $productsPriceWithVat
     * @param int $domainId
     * @param bool $forceFreeTransportAndPayment
     * @return bool
     */
    public function isFree(Money $productsPriceWithVat, int $domainId, bool $forceFreeTransportAndPayment): bool
    {
        if (!$this->customerUserRoleResolver->canCurrentCustomerUserSeePrices()) {
            return false;
        }

        if ($forceFreeTransportAndPayment) {
            return true;
        }

        $freeTransportAndPaymentPriceLimit = $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId);

        if ($freeTransportAndPaymentPriceLimit === null) {
            return false;
        }

        return $productsPriceWithVat->isGreaterThanOrEqualTo($freeTransportAndPaymentPriceLimit);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $productsPriceWithVat
     * @param int $domainId
     * @param bool $forceFreeTransportAndPayment
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getRemainingPriceWithVat(
        Money $productsPriceWithVat,
        int $domainId,
        bool $forceFreeTransportAndPayment,
    ): Money {
        if (!$this->isFree($productsPriceWithVat, $domainId, $forceFreeTransportAndPayment) && $this->isActive($domainId, $forceFreeTransportAndPayment)) {
            return $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId)->subtract($productsPriceWithVat);
        }

        return Money::zero();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    protected function getFreeTransportAndPaymentPriceLimitOnDomain(int $domainId): ?Money
    {
        return $this->pricingSetting->getFreeTransportAndPaymentPriceLimit($domainId);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $productsPrice
     * @param bool $forceFreeTransportAndPayment
     * @return bool
     */
    public function isFreeTransportAndPaymentApplied(
        int $domainId,
        PriceInterface $productsPrice,
        bool $forceFreeTransportAndPayment,
    ): bool {
        return $this->isActive($domainId, $forceFreeTransportAndPayment) && $this->getRemainingPriceWithVat($productsPrice->getPriceWithVat(), $domainId, $forceFreeTransportAndPayment)->isZero();
    }
}
