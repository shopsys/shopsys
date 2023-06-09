<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\NoProductPriceForPricingGroupException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class PriceFactory
{
    /**
     * @param array $pricesArray
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function createProductPriceFromArrayByPricingGroup(
        array $pricesArray,
        PricingGroup $pricingGroup,
    ): ProductPrice {
        foreach ($pricesArray as $priceArray) {
            if ($priceArray['pricing_group_id'] === $pricingGroup->getId()) {
                $priceWithoutVat = Money::create((string)$priceArray['price_without_vat']);
                $priceWithVat = Money::create((string)$priceArray['price_with_vat']);
                $price = new Price($priceWithoutVat, $priceWithVat);
                return new ProductPrice($price, $priceArray['price_from']);
            }
        }

        throw new NoProductPriceForPricingGroupException(0, $pricingGroup->getId());
    }
}
