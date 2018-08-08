<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class ProductVisibilityFactory implements ProductVisibilityFactoryInterface
{
    public function create(
        Product $product,
        PricingGroup $pricingGroup,
        int $domainId
    ): ProductVisibility {
        return new ProductVisibility($product, $pricingGroup, $domainId);
    }
}
