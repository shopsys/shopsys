<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation;

class PromoCodeApplicableProductsTotalPriceCalculator
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     */
    public function calculateTotalPrice(array $quantifiedProducts): PriceInterface
    {
        $domainId = $this->domain->getId();
        $currentCustomer = $this->currentCustomerUser->findCurrentCustomerUser();

        $quantifiedProductsPrices = $this->quantifiedProductPriceCalculation->calculatePrices(
            $quantifiedProducts,
            $domainId,
            $currentCustomer,
        );

        return $this->countTotalPrice($quantifiedProductsPrices);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPriceInterface[] $quantifiedProductsPrices
     */
    protected function countTotalPrice(array $quantifiedProductsPrices): PriceInterface
    {
        $finalPrice = Price::zero();

        foreach ($quantifiedProductsPrices as $quantifiedProductPrice) {
            $finalPrice = $finalPrice->add($quantifiedProductPrice->getTotalPrice());
        }

        return $finalPrice;
    }
}
