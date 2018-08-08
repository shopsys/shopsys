<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductManualInputPriceFactory implements ProductManualInputPriceFactoryInterface
{

    public function create(
        Product $product,
        PricingGroup $pricingGroup,
        ?string $inputPrice
    ): ProductManualInputPrice {
        return new ProductManualInputPrice($product, $pricingGroup, $inputPrice);
    }
}
