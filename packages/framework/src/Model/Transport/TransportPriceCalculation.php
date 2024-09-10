<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class TransportPriceCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver $customerUserRoleResolver
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceFacade $transportPriceFacade
     */
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly TransportPriceFacade $transportPriceFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param int $domainId
     * @param int $cartTotalWeight
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function calculatePrice(
        Transport $transport,
        Price $productsPrice,
        int $domainId,
        int $cartTotalWeight,
    ): Price {
        if ($this->isFree($productsPrice, $domainId)) {
            return Price::zero();
        }

        $transportPrice = $this->transportPriceFacade->getTransportPriceOnDomainByTransportAndClosestWeight($domainId, $transport, $cartTotalWeight);

        return $this->calculateIndependentPrice($transportPrice);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPrice $transportPrice
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function calculateIndependentPrice(TransportPrice $transportPrice): Price
    {
        $domainId = $transportPrice->getDomainId();
        $defaultCurrencyForDomain = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(
            $domainId,
        );
        $vat = $transportPrice->getTransport()->getTransportDomain($domainId)->getVat();

        return $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $transportPrice->getPrice(),
            $this->pricingSetting->getInputPriceType(),
            $vat,
            $defaultCurrencyForDomain,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param int $domainId
     * @return bool
     */
    protected function isFree(Price $productsPrice, int $domainId): bool
    {
        if (!$this->customerUserRoleResolver->canCurrentCustomerUserSeePrices()) {
            return false;
        }

        $freeTransportAndPaymentPriceLimit = $this->pricingSetting->getFreeTransportAndPaymentPriceLimit($domainId);

        if ($freeTransportAndPaymentPriceLimit === null) {
            return false;
        }

        return $productsPrice->getPriceWithVat()->isGreaterThanOrEqualTo($freeTransportAndPaymentPriceLimit);
    }
}
