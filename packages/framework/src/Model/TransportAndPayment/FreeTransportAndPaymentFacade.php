<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\TransportAndPayment;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class FreeTransportAndPaymentFacade
{
    public function __construct(
        protected readonly PricingSetting $pricingSetting,
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
    ) {
    }

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

    public function isFree(PriceInterface $productsPrice, int $domainId, bool $forceFreeTransportAndPayment): bool
    {
        if (!$this->customerUserRoleResolver->canCurrentCustomerUserSeePrices()) {
            return false;
        }

        if ($forceFreeTransportAndPayment) {
            return true;
        }

        $remainingFreeTransportAmount = $this->getDifferenceAmountBetweenLimitAndProductsPrice($productsPrice, $domainId);

        if ($remainingFreeTransportAmount === null) {
            return false;
        }

        return $remainingFreeTransportAmount->isLessThanOrEqualTo(Money::zero());
    }

    public function getRemainingAmount(
        PriceInterface $productsPrice,
        int $domainId,
        bool $forceFreeTransportAndPayment,
    ): Money {
        if (!$this->isFree($productsPrice, $domainId, $forceFreeTransportAndPayment) && $this->isActive($domainId, $forceFreeTransportAndPayment)) {
            return $this->getDifferenceAmountBetweenLimitAndProductsPrice($productsPrice, $domainId);
        }

        return Money::zero();
    }

    protected function getFreeTransportAndPaymentPriceLimitOnDomain(int $domainId): ?Money
    {
        return $this->pricingSetting->getFreeTransportAndPaymentPriceLimit($domainId);
    }

    public function isFreeTransportAndPaymentApplied(
        int $domainId,
        PriceInterface $productsPrice,
        bool $forceFreeTransportAndPayment,
    ): bool {
        return $this->isActive($domainId, $forceFreeTransportAndPayment) && $this->getRemainingAmount($productsPrice, $domainId, $forceFreeTransportAndPayment)->isZero();
    }

    protected function getDifferenceAmountBetweenLimitAndProductsPrice(
        PriceInterface $productsPrice,
        int $domainId,
    ): ?Money {
        $limit = $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId);

        if ($limit === null) {
            return null;
        }

        if ($this->pricingSetting->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            return $limit->subtract($productsPrice->getPriceWithVat());
        }

        return $limit->subtract($productsPrice->getPriceWithoutVat());
    }
}
