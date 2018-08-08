<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductCalculatedPriceFactory implements ProductCalculatedPriceFactoryInterface
{

    /**
     * @param string|null $priceWithVat
     */
    public function create(
        Product $product,
        PricingGroup $pricingGroup,
        ?string $priceWithVat
    ): ProductCalculatedPrice {
        return new ProductCalculatedPrice($product, $pricingGroup, $priceWithVat);
    }
}
