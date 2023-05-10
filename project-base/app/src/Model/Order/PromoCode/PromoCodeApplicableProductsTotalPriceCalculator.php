<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation;

class PromoCodeApplicableProductsTotalPriceCalculator
{
    /**
     * @var \App\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation
     */
    private QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CurrentCustomerUser $currentCustomerUser,
        QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        Domain $domain
    ) {
        $this->currentCustomerUser = $currentCustomerUser;
        $this->quantifiedProductPriceCalculation = $quantifiedProductPriceCalculation;
        $this->domain = $domain;
    }

    /**
     * @param array $quantifiedProducts
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function calculateTotalPrice(array $quantifiedProducts): Price
    {
        $domainId = $this->domain->getId();
        /** @var \App\Model\Customer\User\CustomerUser $currentCustomer */
        $currentCustomer = $this->currentCustomerUser->findCurrentCustomerUser();

        $quantifiedProductsPrices = $this->quantifiedProductPriceCalculation->calculatePrices(
            $quantifiedProducts,
            $domainId,
            $currentCustomer,
        );

        return $this->countTotalPrice($quantifiedProductsPrices);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedProductsPrices
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private function countTotalPrice(array $quantifiedProductsPrices): Price
    {
        $finalPrice = Price::zero();

        foreach ($quantifiedProductsPrices as $quantifiedProductPrice) {
            $finalPrice = $finalPrice->add($quantifiedProductPrice->getTotalPrice());
        }

        return $finalPrice;
    }
}
