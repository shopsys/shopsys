<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

interface ProductCalculatedPriceFactoryInterface
{

    /**
     * @param string|null $priceWithVat
     */
    public function create(
        Product $product,
        PricingGroup $pricingGroup,
        ?string $priceWithVat
    ): ProductCalculatedPrice;
}
