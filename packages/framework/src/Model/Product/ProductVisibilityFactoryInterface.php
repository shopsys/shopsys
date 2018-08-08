<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

interface ProductVisibilityFactoryInterface
{

    public function create(
        Product $product,
        PricingGroup $pricingGroup,
        int $domainId
    ): ProductVisibility;
}
