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
     * @param array<int, array{price_without_vat: mixed, price_with_vat: mixed, price_from: bool, pricing_group_id: int}> $pricesArray
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function createProductPriceFromArrayByPricingGroup(
        array $pricesArray,
        PricingGroup $pricingGroup,
    ): ProductPrice {
        foreach ($pricesArray as $priceArray) {
            if ($priceArray['pricing_group_id'] === $pricingGroup->getId()) {
                return $this->createProductPriceFromArray($priceArray);
            }
        }

        throw new NoProductPriceForPricingGroupException(0, $pricingGroup->getId());
    }

    /**
     * @param array{price_without_vat: mixed, price_with_vat: mixed, price_from: bool} $priceArray
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function createProductPriceFromArray(array $priceArray): ProductPrice
    {
        $price = $this->createPriceFromArray($priceArray);

        return new ProductPrice($price, $priceArray['price_from']);
    }

    /**
     * @param array{price_without_vat: mixed, price_with_vat: mixed} $priceArray
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function createPriceFromArray(array $priceArray): Price
    {
        $priceWithoutVat = Money::create((string)$priceArray['price_without_vat']);
        $priceWithVat = Money::create((string)$priceArray['price_with_vat']);

        return new Price($priceWithoutVat, $priceWithVat);
    }
}
